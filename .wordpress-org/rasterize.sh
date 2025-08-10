#!/bin/bash

if ! command -v rsvg-convert >/dev/null 2>&1; then
	echo "Error: The rsvg-convert  is not available."
	echo "On macOS, you can install it with Homebrew: brew install librsvg"
	exit 1
fi

if ! command -v oxipng >/dev/null 2>&1; then
	echo "Error: The oxipng  is not available."
	echo "On macOS, you can install it with Homebrew: brew install oxipng"
	exit 1
fi

for size in 128 256; do
	png_file="icon-${size}x${size}.png"
	rsvg-convert -w $size -h $size -o "$png_file" icon.svg
	oxipng --opt 6 --strip all "$png_file"
done

rsvg-convert -w 772 -h 250 -o "banner-772x250.png" banner.svg
oxipng --opt 6 --strip all "banner-772x250.png"

rsvg-convert -w 1544 -h 500 -o "banner-1544x500.png" banner.svg
oxipng --opt 6 --strip all "banner-1544x500.png"

rsvg-convert -w 1280 -h 640 -o "banner-github.png" banner-github.svg
oxipng --opt 6 --strip all "banner-github.png"
