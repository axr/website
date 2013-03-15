#!/usr/bin/python

import json
import sys

import pprint

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

try:
	data = json.load(open(sys.argv[1]))
except ValueError:
	sys.exit(1)

keys = sys.argv[2].split('.')
value = get_key_value(data, keys)

if value is not None:
	print(value)
else:
	sys.exit(1)
