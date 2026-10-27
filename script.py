import re
import sys

def main():
    try:
        with open('index.htm', 'r', encoding='utf-8') as f:
            data = f.read()
            
        inputs = re.findall(r'<input[^>]*id=["\']([^"\']+)["\'][^>]*>', data)
        for i in inputs:
            print(i)
    except Exception as e:
        print(e)

if __name__ == "__main__":
    main()
