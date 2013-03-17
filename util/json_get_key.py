#!/usr/bin/python

import json
import sys
import os
import pprint

def read_json (path):
	try:
		return json.load(open(path))
	except ValueError:
		pass
	except (OSError, IOError):
		pass

	return {}

def merge_dicts (dict1, dict2):
	for key in dict2.keys():
		if key in dict1 and type(dict1[key]) is dict and type(dict2[key]) is dict:
			merge_dicts(dict1[key], dict2[key])
			continue

		dict1[key] = dict2[key]

def get_key_value (data, keys):
	if len(keys) is 0:
		return None

	key = keys[0]

	if type(data) is dict and key in data:
		if isinstance(data[key], (dict, list)):
			keys.pop(0)
			return get_key_value(data[key], keys)

		return data[key]

	if type(data) is list:
		if len(keys) > 1:
			if key.isdigit() and len(data) > int(key):
				keys.pop(0)
				return get_key_value(data[int(key)], keys)
		elif key.isdigit() and len(data) > int(key):
			return data[int(key)]

	return None

if len(sys.argv) is not 3:
	sys.exit(1)

data = {}
merge_dicts(data, read_json(os.path.dirname(os.path.abspath(sys.argv[1])) + "/config.default.json"))
merge_dicts(data, read_json(sys.argv[1]))

keys = sys.argv[2].split('.')
value = get_key_value(data, keys)

if value is not None:
	print(value)
else:
	sys.exit(1)
