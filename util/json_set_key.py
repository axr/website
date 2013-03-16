#!/usr/bin/python

import json
import sys

def set_key_value (data, keys, write):
	key = keys[0]

	if type(data) is dict:
		if len(keys) > 1:
			if key not in data or not isinstance(data[key], (list, dict)):
				if keys[1].isdigit():
					data[key] = []
				else:
					data[key] = {}

			keys.pop(0)
			return set_key_value(data[key], keys, write)

		data[key] = write
		return True

	if type(data) is list and key.isdigit():
		key = int(key)

		if key is 0:
			data.append(write)
			return True

		if key < len(data):
			data[key] = write
			return True

	return False

if len(sys.argv) is not 4:
	sys.exit(1)

try:
	data = json.load(open(sys.argv[1]))
except ValueError:
	data = {}
except (OSError, IOError):
	data = {}

keys = sys.argv[2].split('.')
write = sys.argv[3]

if keys[0] == "prod":
	write = False
	if write is True:
		write = True

status = set_key_value(data, keys, write)

if status is False:
	sys.exit(1)

with open(sys.argv[1], 'w') as f:
	f.write(json.dumps(data, indent=4))
