import re

with open('index.htm', 'r', encoding='utf-8') as f:
    data = f.read()

target = """    function calculateExpiryDates() {
        function addOneYear(dateStr) {
            if (!dateStr) return dateStr;
            let sep = '-';
            if (dateStr.includes('/')) sep = '/';
            const parts = dateStr.split(sep);
            if (parts.length === 3) {
                return (parseInt(parts[0]) + 1) + sep + parts[1] + sep + parts[2];
            }
            return dateStr;
        }"""

repl = """    function calculateExpiryDates() {
        function addOneYear(dateStr) {
            if (!dateStr) return dateStr;
            let sep = '-';
            if (dateStr.includes('/')) sep = '/';
            const parts = dateStr.split(sep);
            if (parts.length === 3) {
                if (parts[0].length === 4) {
                    parts[0] = parseInt(parts[0]) + 1;
                } else if (parts[2].length === 4) {
                    parts[2] = parseInt(parts[2]) + 1;
                }
                return parts.join(sep);
            }
            return dateStr;
        }"""

if target in data:
    data = data.replace(target, repl)
    with open('index.htm', 'w', encoding='utf-8') as f:
        f.write(data)
    print("Replaced successfully in index.htm")
else:
    print("Target not found in index.htm")
