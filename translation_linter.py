from pathlib import Path
from dataclasses import dataclass, field
import re
from typing import TypeVar
import itertools as it


@dataclass
class FrameworkUsageInstance:
    php_file_name: str
    php_file_line_number: int
    php_file_line_content: str

    def __str__(self) -> str:
        return f"{self.php_file_name}#{self.php_file_line_number} -> {self.php_file_line_content.strip()}"


def find_framework_instance(
    all_php_files: list[Path], what_to_look_for: list[re.Pattern]
) -> list[FrameworkUsageInstance]:
    all_usages: list[FrameworkUsageInstance] = []
    for php_file in all_php_files:
        # Skipping "language.php" as it is where framework is set up
        if php_file.name == "language.php":
            continue

        with open(php_file, "r") as f:
            php_file_lines = f.readlines()
        for php_file_line_number, php_file_line_content in enumerate(
            php_file_lines, start=1
        ):
            for re_to_look_for in what_to_look_for:
                if re_to_look_for.search(php_file_line_content):
                    all_usages.append(
                        FrameworkUsageInstance(
                            php_file_name=php_file.name,
                            php_file_line_number=php_file_line_number,
                            php_file_line_content=php_file_line_content,
                        )
                    )
    return all_usages


def report_framework_v1(all_php_files: list[Path]) -> None:
    print("=====> Framework v1 aka $language <=====")
    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files, what_to_look_for=[re.compile(r"\$language")]
    )
    print(f"Found {len(all_usages)} instances of usage of framework v1")

    # TODO: (when framework v3 has stabilized)
    # - report warning for each usage
    # - report error for each "simple" usage that could easily be converted


def report_framework_v2(all_php_files: list[Path]) -> None:
    print("=====> Framework v2 aka gettext <=====")
    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files,
        what_to_look_for=[
            re.compile(r" _\("),
            re.compile(r" P_\("),
            re.compile(r" F_\("),
            re.compile(r" FN_\("),
        ],
    )
    print(f"Found {len(all_usages)} instances of usage of framework v2")

    # TODO: (when framework v3 has stabilized)
    # - report error for each usage


class TranslationTableParseError(Exception):
    pass


@dataclass
class ParsedTranslationTableEntry:
    @dataclass
    class ParsedTranslationTableEntryTranslation:
        language: str
        content: str

    translation_key: str
    translations: list[ParsedTranslationTableEntryTranslation] = field(
        default_factory=list
    )


def parse_static_translation_table(
    raw_content: list[str],
) -> list[ParsedTranslationTableEntry]:
    # Limited parser ability, let's assume this .php file is valid php.
    # This parser is also extremely strict regarding its definitions.

    DEFINE_TOKEN = "define("
    define_found = False

    TRANSLATION_TABLE_NAME_TOKEN = '"TRANSLATION_TABLE",'
    translation_table_name_found = False

    BEGIN_ARRAY_TOKEN = "array("
    begin_array_token_found = False

    BEGIN_TRANSLATION_DEFINITION_TOKEN = re.compile(r'"([a-zA-Z0-9_]*)" => array\(')
    begin_translation_definition_found = False
    begin_translation_definition_value = None

    TRANSLATION_DEFINITION_TOKEN = re.compile(r'"([a-z]{2})" => "(.*)",$')

    END_TRANSLATION_DEFINITION_TOKEN = re.compile(r"\),")

    END_ARRAY_TOKEN = ")"

    END_DEFINE_TOKEN = ");"

    parsed_translation_table: list[ParsedTranslationTableEntry] = []
    current_translation_entry = None

    for line_number, line in enumerate(raw_content, start=1):
        line = line.strip()

        # Skip whitespace
        if line == "":
            continue

        # Skip PHP opening tag
        if line == "<?php":
            continue

        # Skip comments (can't handle multiline comments for now)
        if line.startswith("/*") or line.endswith("*/") or line.startswith("//"):
            continue

        # Ignore everything till we find the DEFINE token
        if not define_found and line != DEFINE_TOKEN:
            raise TranslationTableParseError(
                f"On line {line_number}, expected {DEFINE_TOKEN}, found: {line}"
            )
        elif line == DEFINE_TOKEN:
            define_found = True
            continue

        # Ignore everything till we find the table name
        if not translation_table_name_found and line != TRANSLATION_TABLE_NAME_TOKEN:
            raise TranslationTableParseError(
                f"On line {line_number}, expected {TRANSLATION_TABLE_NAME_TOKEN}, found: {line}"
            )
        elif line == TRANSLATION_TABLE_NAME_TOKEN:
            translation_table_name_found = True
            continue

        # Ignore everything till we find the beginning of the array
        if not begin_array_token_found and line != BEGIN_ARRAY_TOKEN:
            raise TranslationTableParseError(
                f"On line {line_number}, expected {BEGIN_ARRAY_TOKEN}, found: {line}"
            )
        elif line == BEGIN_ARRAY_TOKEN:
            begin_array_token_found = True
            continue

        # At this point, we're starting to read translations

        # First line must be a declaration for a new entry...
        # or the end of all entries
        if not begin_translation_definition_found:
            if match := BEGIN_TRANSLATION_DEFINITION_TOKEN.match(line):
                begin_translation_definition_found = True
                begin_translation_definition_value = match.group(1)
                current_translation_entry = ParsedTranslationTableEntry(
                    translation_key=begin_translation_definition_value
                )
                parsed_translation_table.append(current_translation_entry)
                continue
            elif line == END_ARRAY_TOKEN:
                continue
            elif line == END_DEFINE_TOKEN:
                continue
            else:
                raise TranslationTableParseError(
                    f"On line {line_number}, expected {BEGIN_TRANSLATION_DEFINITION_TOKEN}, found: {line}"
                )

        # Then, a variable number of lines indicating a translation
        if match := TRANSLATION_DEFINITION_TOKEN.match(line):
            language = match.group(1)
            content = match.group(2)

            assert current_translation_entry is not None
            current_translation_entry.translations.append(
                ParsedTranslationTableEntry.ParsedTranslationTableEntryTranslation(
                    language=language, content=content
                )
            )
            continue
        elif match := END_TRANSLATION_DEFINITION_TOKEN.match(line):
            begin_translation_definition_found = False
            begin_translation_definition_value = None
            current_translation_entry = None
            continue
        else:
            raise TranslationTableParseError(
                f"On line {line_number}, expected {TRANSLATION_DEFINITION_TOKEN} or {END_TRANSLATION_DEFINITION_TOKEN}, found: {line}"
            )

    return parsed_translation_table


T = TypeVar("T")


def find_duplicates_in_list(l: list[T]) -> set[T]:
    seen = set()
    duplicates = set()
    for i in l:
        if i not in seen:
            seen.add(i)
        else:
            duplicates.add(i)
    return duplicates


@dataclass
class FrameworkV3Usages:
    @dataclass
    class SimpleFrameworkV3Usage(FrameworkUsageInstance):
        entry_key: str

    @dataclass
    class FormatFrameworkV3Usage(FrameworkUsageInstance):
        entry_key: str
        parameters: list[tuple[str, str]]  # name + value

    simple_usages: list[SimpleFrameworkV3Usage] = field(default_factory=list)  # t()
    format_usages: list[FormatFrameworkV3Usage] = field(default_factory=list)  # Ft()


def parse_usages(all_usages: list[FrameworkUsageInstance]) -> FrameworkV3Usages:
    SIMPLE_USAGE_TOKEN = re.compile(r'[ (]t\("([a-zA-Z0-9_ :]*)"\)')
    FORMAT_USAGE_TOKEN = re.compile(r'[ (]Ft\("([a-zA-Z0-9_ :]*)", ([^)]*)\)')

    framework_usages = FrameworkV3Usages()

    for usage in all_usages:
        if match := SIMPLE_USAGE_TOKEN.search(usage.php_file_line_content):
            framework_usages.simple_usages.append(
                FrameworkV3Usages.SimpleFrameworkV3Usage(
                    php_file_name=usage.php_file_name,
                    php_file_line_number=usage.php_file_line_number,
                    php_file_line_content=usage.php_file_line_content,
                    entry_key=match.group(1),
                )
            )
        elif match := FORMAT_USAGE_TOKEN.search(usage.php_file_line_content):
            params = match.group(2).split(",")
            params_name_value = []
            for param in params:
                param_split = param.split(":")
                params_name_value.append(
                    (param_split[0].strip(), param_split[1].strip())
                )
            framework_usages.format_usages.append(
                FrameworkV3Usages.FormatFrameworkV3Usage(
                    php_file_name=usage.php_file_name,
                    php_file_line_number=usage.php_file_line_number,
                    php_file_line_content=usage.php_file_line_content,
                    entry_key=match.group(1),
                    parameters=params_name_value,
                )
            )
        else:
            print("Warning! Could not parse usage, will be ignored: ", usage)

    return framework_usages


class TranslationTableSanityError(Exception):
    pass


class LinterError(Exception):
    pass


def report_framework_v3(all_php_files: list[Path]) -> None:
    print("=====> Framework v3 aka static translation table <=====")

    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files,
        what_to_look_for=[re.compile(r"[ (]t\("), re.compile(r"[ (]Ft\(")],
    )
    print(f"Found {len(all_usages)} instances of usage of framework v3")

    print("-> Loading translation key file...")
    with open("php/includes/static_translation_table.php") as f:
        parsed_static_translation_table = parse_static_translation_table(f.readlines())

    print("-> Sanity check: unicity of entries")
    if duplicates := find_duplicates_in_list(
        [entry.translation_key for entry in parsed_static_translation_table]
    ):
        raise TranslationTableSanityError(
            f"Found duplicate keys in translation table: {duplicates}"
        )

    print("-> Sanity check: unicity of translations for a given entry")
    for entry in parsed_static_translation_table:
        if duplicates := find_duplicates_in_list(
            [translation.language for translation in entry.translations]
        ):
            raise TranslationTableSanityError(
                f"Found duplicate translations for entry {entry.translation_key}: {duplicates}"
            )

    # TODO: check that keys must be in alphabetical order?

    print("-> Converting raw parsed structure in usable structure")
    translation_table = {
        entry.translation_key: {
            translation.language: translation.content
            for translation in entry.translations
        }
        for entry in parsed_static_translation_table
    }

    print("-> Parsing usages")
    parsed_usages = parse_usages(all_usages)

    print("-> Checking that, for each usage, translation key does exist")
    for usage in it.chain(parsed_usages.simple_usages, parsed_usages.format_usages):
        if usage.entry_key not in translation_table:
            raise LinterError(
                f"Could not find entry {usage.entry_key} for usage {usage}"
            )

    print("-> Checking that translations for french and english are available")
    for entry, translations in translation_table.items():
        for language in ("en", "fr"):
            if language not in translations.keys():
                raise LinterError(
                    f"Missing translation in {language} for entry {entry}"
                )

    print("-> Checking that for parameters usages, parameters actually exist")
    for usage in parsed_usages.format_usages:
        for parameter in usage.parameters:
            entry = translation_table[usage.entry_key]
            for language in entry:
                if f"{{{parameter[0]}}}" not in entry[language]:
                    raise LinterError(
                        f"Parameter {parameter[0]} not found in entry {usage.entry_key} for language {language} and for usage '{usage}'"
                    )

    # TODO: check that no parameters are forgotten ?
    # TODO: check that all keys are used ?


def main():
    print("Building list of all .php files...")
    all_php_files = list(Path(".").glob("**/*.php"))
    report_framework_v1(all_php_files=all_php_files)
    report_framework_v2(all_php_files=all_php_files)
    report_framework_v3(all_php_files=all_php_files)


if __name__ == "__main__":
    main()
