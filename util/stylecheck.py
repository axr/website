#!/usr/bin/env python

from __future__ import print_function
from subprocess import check_output
import json
import os
import re
import sys

# http://stackoverflow.com/a/3002505/343845
def is_binary(filename):
    """Return true if the given filename is binary.
    @raise EnvironmentError: if the file does not exist or cannot be accessed.
    @attention: found @ http://bytes.com/topic/python/answers/21222-determine-file-type-binary-text on 6/08/2010
    @author: Trent Mick <TrentM@ActiveState.com>
    @author: Jorge Orpinel <jorge@orpinel.com>"""
    fin = open(filename, 'rb')
    try:
        CHUNKSIZE = 1024
        while 1:
            chunk = fin.read(CHUNKSIZE)
            if b'\0' in chunk: # found null byte
                return True
            if len(chunk) < CHUNKSIZE:
                break # done
    finally:
        fin.close()

    return False

# Get a list of files thare have been edited
def get_edited_files ():
    git_status = check_output(["git", "status", "-s", "--porcelain"])\
        .decode('utf-8')
    return re.findall('^[AM][AMD]?\s+([a-zA-Z0-9-_/.]+)$', git_status, re.MULTILINE)

# Check if file ends with exactly one blank line
def check_file_ending (data):
    problems = []

    if not data.endswith("\n\n"):
        problems.append("File does not end with a blank line")

    if data.endswith("\n\n\n"):
        problems.append("File must end with only one blank line")

    return problems

# Check .css files
def check_file_css (data):
    return check_file_ending(data)

# Check .js files
def check_file_js (data):
    problems = []
    lines = data.split("\n")

    counter = 0
    for line in lines:
        counter += 1

        if re.search("console.log\s*\(", line):
            problems.append("console.log found on line %i" % counter)

    return problems + check_file_ending(data)

# Check .php files
def check_file_php (data):
    problems = []
    lines = data.split("\n")

    counter = 0
    for line in lines:
        counter += 1

        if re.search("var_dump\s*\(", line):
            problems.append("var_dump found on line %i" % counter)

    if re.search("(\n)\s*\?\>", data):
        problems.append("You must not close PHP tags")

    return problems + check_file_ending(data)

# Check .html files
def check_file_html (data):
    return check_file_ending(data)

# Check .json files
def check_file_json (data):
    problems = []

    try:
        json.loads(data)
    except ValueError:
        problems.append("File contains invalid JSON")

    return problems + check_file_ending(data)

if __name__ == "__main__":
    files = get_edited_files()
    problems = {}

    print("Running code style checker...")

    for path in files:
        root, extension = os.path.splitext(path)

        if is_binary(path):
            continue

        with open(path, "r+") as f:
            data = "".join(f.readlines())

            problems[path] = []

            if extension == ".css":
                problems[path] += check_file_css(data)
            elif extension == ".js":
                problems[path] += check_file_js(data)
            elif extension == ".php":
                problems[path] += check_file_php(data)
            elif extension == ".html":
                problems[path] += check_file_html(data)
            elif extension == ".json":
                problems[path] += check_file_json(data)

            if len(problems[path]) == 0:
                problems.pop(path)

    for path in problems:
        print("\n" + path)

        for problem in problems[path]:
            print ("- " + problem)

    sys.exit((int) (len(problems) > 0))

