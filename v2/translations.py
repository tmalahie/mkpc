import os
import re
import argparse
import json

parser = argparse.ArgumentParser()
parser.add_argument("-i", required=False)
parser.add_argument("-o", required=False)
parser.add_argument("-f", required=False)
args = parser.parse_args()
inputfile = args.i
outputfile = args.o

def list_dir_files(path):
    """
    List all files in a directory, recursively.
    """
    for root, dirs, files in os.walk(path):
        for file in files:
            yield os.path.join(root, file)

if not inputfile:
  files = list_dir_files("app/pages")
  print("\n".join(files))
  exit()

def read_file_content(path):
  """
  Read the content of a file.
  """
  with open(path, 'r') as f:
    return f.read()

def write_file_content(path, content):
  """
  Write the content to a file. If file does not exist, create it
  """
  with open(path, 'w') as f:
    f.write(content)

def trim(str):
  return re.sub(' +', ' ', str)

def lower_except_first_letter(str):
  return str[0] + str[1:].lower()

language_pattern = r"language \? (['\"])(.+?)\1 : (['\"])(.+?)\3"
not_alphanum = r"[^a-zA-Z0-9_ ]"
translations = {
  "en": {},
  "fr": {},
}
if outputfile:
  for lang in ["fr", "en"]:
    outputfile_full = "app/public/locales/"+ lang + "/" + outputfile + ".json"
    if os.path.isfile(outputfile_full):
      translations[lang] = json.loads(read_file_content(outputfile_full))
#for file in files:
for file in [inputfile]:
  print(file)
  file_content = read_file_content(file)
  
  language_occurences = re.finditer(language_pattern, file_content)
  new_file_content = ""
  last_end = 0
  for occurence_match in language_occurences:
    occurence = occurence_match.groups()
    start = occurence_match.start()
    end = occurence_match.end()
    en = occurence[1]
    fr = occurence[3]
    print("EN:"+en)
    print("FR:"+fr)
    key = re.sub(not_alphanum, "", lower_except_first_letter(trim(en.replace(" ", " ").replace("-", " ")).replace(":", " ")))
    key_separators = key.split(" ")
    new_key = ""
    for key_separator in key_separators:
      if new_key != "":
        new_key += "_"
      new_key += key_separator
      if len(new_key) > 20:
        break
    print("Key:"+str(new_key))
    if not new_key:
      print("Warning: empty key")
      input()
    if new_key in translations["fr"] and translations["fr"][new_key] != fr:
      print("")
      print("Conflict: "+new_key)
      print("FR[before]: "+translations["fr"][new_key])
      print("FR[after]:"+fr)
      print("")
      input()
    elif new_key in translations["en"] and translations["en"][new_key] != en:
      print("")
      print("Conflict: "+new_key)
      print("EN[before]: "+translations["en"][new_key])
      print("EN[after]:"+en)
      print("")
      input()
    else:
      translations["fr"][new_key] = fr
      translations["en"][new_key] = en
      replacement = 't("' + new_key + '")'
      new_file_content += file_content[last_end:start] + replacement
      last_end = end
new_file_content += file_content[last_end:]

if args.f and inputfile and outputfile:
  for lang in ["fr", "en"]:
    outputfile_full = "app/public/locales/"+ lang + "/" + outputfile + ".json"
    write_file_content(outputfile_full, json.dumps(translations[lang], indent=2))
  write_file_content(inputfile, new_file_content)
else:
  print()
  for lang in ["fr", "en"]:
    outputfile_full = "app/public/locales/"+ lang + "/" + outputfile + ".json"
    print(outputfile_full)
    print(translations[lang])
    input()
  print(outputfile)
  print(new_file_content)