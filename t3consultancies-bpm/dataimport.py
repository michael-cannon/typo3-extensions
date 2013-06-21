#!/usr/bin/env python

from __future__ import generators

import MySQLdb
import shutil
import os.path
import time

# Set for python 2.2 implementation from 4Suite (GPL 2)
# http://cvs.4suite.org/viewcvs/4Suite/Ft/Rdf/ThirdParty/n3p/n3p.py?rev=1.2&content-type=text/vnd.viewcvs-markup
try:
   set()
except NameError:
    try:
        from sets import Set as set
    except ImportError:
        # Minimal implementation of set
        class set(object):

            __slots__ = ['_data']

            def __init__(self, iterable):
                self._data = data = {}
                for element in iterable:
                    data[element] = True
                return

            def __contains__(self, element):
                return element in self._data

            def add(self, element):
                self._data[element] = True

            def __iter__(self):
                for x in self._data.iterkeys():
                    yield x

# change database and other config here
# settings for database to export from
expuser = 'bpm_locator2dev4'
expdatabase = 'bpm_locator2dev4'
exppassword = 'nw30MrjL'
# settings for database to import to
impuser = 'bpm_typo3dev4'
impdatabase = 'bpm_typo3dev4'
imppassword = 'nw30MrjL'
# other settings
pid = 2
pid = 122
# with slash
imppath = "../../../uploads/tx_t3consultancies/"
# without slash
exppath = "/home/markus/bpmlogos"
exppath = "../../../solutions"


if exppassword is None:
    econn = MySQLdb.connect(user = expuser)
else:
    econn = MySQLdb.connect(user = expuser, passwd = exppassword)
econn.select_db(expdatabase)

if imppassword is None:
    iconn = MySQLdb.connect(user = impuser)
else:
    iconn = MySQLdb.connect(user = impuser, passwd = imppassword)
iconn.select_db(impdatabase)

ecurs = econn.cursor()
icurs = iconn.cursor()

#icurs.execute("DELETE FROM tx_t3consultancies_services_mm")
#icurs.execute("DELETE FROM tx_t3consultancies_cat")
#icurs.execute("DELETE FROM tx_t3consultancies")

categories = {}
ecurs.execute("SELECT keyid, resultstring FROM optionkeys WHERE keyid LIKE 'product-%' ORDER BY keyid")
for row in ecurs.fetchall():
    title = row[1].replace("XML ? EDI", "XML / EDI")
    icurs.execute("SELECT uid FROM tx_t3consultancies_cat WHERE title = %s", (title,))
    if icurs.rowcount > 0:
        categories[row[0].split('-')[1]] = icurs.fetchone()[0]
    else:
        if row[0] != 'product-b':
            icurs.execute("INSERT INTO tx_t3consultancies_cat (pid, title) VALUES (%s, %s)", (pid, title))
        categories[row[0].split('-')[1]] = icurs.lastrowid
    
ecurs.execute("SELECT compname, url, imageurl, description, level, firstname, lastname, email, services, pause, sdate, edate FROM vendor")
for row in ecurs.fetchall():

    # Adjust level
    level = 0
    if row[4] == 1:
        level = 100

    # Fix URL
    url = row[1]
    if not url.startswith('http://'):
        url = "http://%s" % url

    # Copy logo file
    logo = os.path.basename(row[2])
    shutil.copyfile(exppath + row[2], imppath + logo)

    # Adjust services -> categories
    services = row[8].split('-')[1:]
    # Merge the two Packaged Applications Software categories
    services1 = []
    for service in services:
        if service == 'b':
            services1.append('a')
        else:
            services1.append(service)
    services = set(services1)
    cats = [categories[x] for x in services]

    hidden = 0
    if row[9] == 'Yes':
        hidden = 1

    sdate = row[10]
    edate = row[11]
    try:
        starttime = int(time.mktime(sdate.timetuple()))
    except AttributeError:
        # handle NULL in sdate
        starttime = 0
    try:
        if edate.year > 2200:
            edate = edate.replace(year = edate.year - 200)
        endtime = int(time.mktime(edate.timetuple()))
    except AttributeError:
        # handle NULL in edate
        endtime = 0

    icurs.execute("""
        INSERT INTO tx_t3consultancies (pid, title, url, logo, description, weight, contact_name, contact_email, hidden, starttime, endtime, selected)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, 1)""",
        (pid, row[0], row[1], logo, row[3], level, "%s %s" % (row[5], row[6]), row[7], hidden, starttime, endtime))
    uid = icurs.lastrowid
    for cat in cats:
        icurs.execute("""
            INSERT INTO tx_t3consultancies_services_mm(uid_local, uid_foreign)
            VALUES (%s, %s)""",
            (uid, cat))
