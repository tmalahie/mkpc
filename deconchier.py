import os

files = []

for filename in os.listdir("."):
	if filename.endswith(".php"):
		files.append(filename)

for filename in files:
	with open(filename) as f:
		try:
			s = f.read()
		except:
			continue
		if "initdb" in s and not "mysql_close" in s:
			print(filename)
			input()