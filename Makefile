PHP_SOURCES := $(shell find -name '*.php')
PO_FILES := $(shell find -name '*.po')

.SUFFIXES:

# Adding a new language:
#  mkdir -pv po/zh_CH/
#  msginit --input=po/mkpc.pot --locale=zh --output=po/zh_CH/mkpc.po

# Keywords for gettext.
# This is what allows gettext to figure out that _("aaa") means that
# the string "aaa" needs to be translated.
# https://www.gnu.org/software/gettext/manual/html_node/Default-Keywords.html
GETTEXT_KEYWORDS = -kP_:1c,2 -kF_:1 -kFN_:1,2

all: po/fr_FR/LC_MESSAGES/mkpc.mo msgcheck server-restart



# PO template file, from which all PO files are built
po/mkpc.pot: $(PHP_SOURCES) Makefile
	xgettext $(GETTEXT_KEYWORDS) -c -o po/mkpc.pot $(PHP_SOURCES)

# PO file, one per locale.
# Ensure that PO file is always updated by this step.
po/fr_FR/mkpc.po: po/mkpc.pot
	msgmerge --update --backup=off $@ $<
	touch $@

# MO file, PO files compiled for quick access
po/fr_FR/LC_MESSAGES/mkpc.mo: po/fr_FR/mkpc.po po/fr_FR/LC_MESSAGES
	msgfmt --output-file=$@ $<

# gettext will look for .mo files here
po/fr_FR/LC_MESSAGES:
	mkdir -pv $@

# Additional checks on PO files for validation
msgcheck:
	msgcheck $(PO_FILES)

# gettext has an internal cache.
# If the .mo files are changed, the web server needs to be restarted
server-restart: po/fr_FR/LC_MESSAGES/mkpc.mo
	docker exec -ti mkpc_web apachectl -k graceful
