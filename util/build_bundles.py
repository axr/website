#!/usr/bin/python

import http.client
import json
import os
import shutil
import tempfile
import urllib.parse
from deps import cssmin

PATH_STATIC = '../www/static'
PATH_DRUPAL = '../www'

def process_js (data):
    '''
    Minify JavaScript code using Google Closure compiler
    TODO: Use a local fallback methos when Closure is not available

    Args:
    - string data: JavaScript code to minify

    Return: string
    '''
    params = urllib.parse.urlencode([
        ('js_code', data),
        ('compilation_level', 'SIMPLE_OPTIMIZATIONS'),
        ('output_format', 'text'),
        ('output_info', 'compiled_code'),
    ])

    headers = {
        'Content-type': 'application/x-www-form-urlencoded'
    }

    connection = http.client.HTTPConnection('closure-compiler.appspot.com')
    connection.request('POST', '/compile', params, headers)

    response = connection.getresponse()
    data = str(response.read(), 'utf-8')

    connection.close()

    return data

def process_css (data):
    '''
    Minify CSS code using an edited version of the cssmin module

    Args:
    - string data: CSS code to minify

    Return: string
    '''
    return cssmin.cssmin(data)

# Make sure the target directory doesn't exist
if os.path.isdir('./bundles'):
    answer = input('Directory "./bundles" already exists. Overwrite? ')

    if answer[0] != 'y' and answer[0] != 'Y':
        print('Directory already exists. Exiting...')
        exit(1)

    shutil.rmtree('./bundles/')

# Prepare dierctories
os.makedirs('./bundles/')
os.makedirs('./bundles/css/')
os.makedirs('./bundles/js/')

# Read bundles info file
bundles = open('../shared/bundles.json', 'r').read()
bundles = json.loads(bundles)

for bundleName in bundles:
    print('Building bundle ' + bundleName)

    bundle = bundles[bundleName]
    bundleData = ''

    # Concatenate all bundle files
    for fileName in bundle['files']:
        if fileName[0:8] == '{DRUPAL}':
            fileName = fileName.replace('{DRUPAL}', PATH_DRUPAL)
        else:
            fileName = PATH_STATIC + '/' + fileName

        bundleData += open(fileName).read() + '\n\n'

    # Process the resulting bunble file
    if bundle['type'] == 'js':
        bundleData = process_js(bundleData)
    elif bundle['type'] == 'css':
        bundleData = process_css(bundleData)

    # Write the bundle into a file
    f = open('./bundles/' + bundleName, 'w')
    f.write(bundleData)
    f.close()

print('Done')

