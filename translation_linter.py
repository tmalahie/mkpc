from pathlib import Path
from dataclasses import dataclass, field
import re


@dataclass
class FrameworkUsageInstance:
    php_file_name: str
    php_file_line_number: int
    php_file_line_content: str

    def __str__(self) -> str:
        return f"{self.php_file.name}#{self.php_file_line_number} -> {self.php_file_line_content.strip()}"


def find_framework_instance(
    all_php_files: list[Path], what_to_look_for: list[str]
) -> list[FrameworkUsageInstance]:
    all_usages: list[FrameworkUsageInstance] = []
    for php_file in all_php_files:
        # Skipping "language.php" as it is where framework is set up
        if php_file.name == "language.php":
            continue

        with open(php_file, "r") as f:
            php_file_lines = f.readlines()
        for php_file_line_number, php_file_line_content in enumerate(php_file_lines):
            for str_to_look_for in what_to_look_for:
                if str_to_look_for in php_file_line_content:
                    all_usages.append(
                        FrameworkUsageInstance(
                            php_file_name=php_file.name,
                            php_file_line_number=php_file_line_number,
                            php_file_line_content=php_file_line_content,
                        )
                    )
    return all_usages


def report_framework_v1(all_php_files: list[Path]) -> None:
    print("-- Framework v1 aka $language --")
    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files, what_to_look_for=["$language"]
    )
    print(f"Found {len(all_usages)} instances of usage of framework v1")

    # TODO: (when framework v3 has stabilized)
    # - report warning for each usage
    # - report error for each "simple" usage that could easily be converted


def report_framework_v2(all_php_files: list[Path]) -> None:
    print("-- Framework v2 aka gettext --")
    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files, what_to_look_for=["_("]
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
        translation: str

    translation_key: str
    translations: list[ParsedTranslationTableEntryTranslation] = field(
        default_factory=list
    )


def parse_static_translation_table(raw_content: list[str]) -> list[ParsedTranslationTableEntry]:
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

    TRANSLATION_DEFINITION_TOKEN = re.compile(r'"([a-z]{2})" => "([^"]*)"')

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
            translation_language = match.group(1)
            translation = match.group(2)

            current_translation_entry.translations.append(
                ParsedTranslationTableEntry.ParsedTranslationTableEntryTranslation(
                    language=translation_language, translation=translation
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


def report_framework_v3(all_php_files: list[Path]) -> None:
    print("-- Framework v3 aka static translation table --")

    print("-> Finding all usages")
    all_usages = find_framework_instance(
        all_php_files=all_php_files, what_to_look_for=[" t(", " Ft("]
    )
    print(f"Found {len(all_usages)} instances of usage of framework v3")

    print("-> Loading translation key file...")
    with open("php/includes/static_translation_table.php") as f:
        parse_static_translation_table(f.readlines())

    print("-> Checking sanity of translation key file...")
    # TODO: sanity checks for the static definition file before progressing
    # - each translation entry 
    raise NotImplementedError


    # TODO: rules for linter tool
    # - all keys must be used
    # - keys must be in alphabetical order
    # - translation for "en" and "fr" must be available
    # - for each call to translation function: key must exist
    # - for formatted calls: params must exist in the string

    # key existence
    print("-> Checking that, for each usage, translation key does exist...")
    for usage in all_usages:
        raise NotImplementedError


def main():
    print("Building list of all .php files...")
    all_php_files = list(Path(".").glob("**/*.php"))
    report_framework_v1(all_php_files=all_php_files)
    report_framework_v2(all_php_files=all_php_files)
    report_framework_v3(all_php_files=all_php_files)


if __name__ == "__main__":
    main()
