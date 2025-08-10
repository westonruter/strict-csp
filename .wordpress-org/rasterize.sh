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
