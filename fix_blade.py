import re
path = 'resources/views/focus_area/show.blade.php'
with open(path, 'r') as f:
    content = f.read()

# find all {{ e($var) }} and replace with {{ $var }}
content = re.sub(r'\{\{\s*e\(\s*\$([^)]+)\s*\)\s*\}\}', r'{{ $\1 }}', content)

with open(path, 'w') as f:
    f.write(content)
print('Replaced successfully!')
