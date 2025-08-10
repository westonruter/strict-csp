#!/bin/bash

set -e

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

cd "$(dirname "$0")"

rasterize() {
	local width=$1
	local height=$2
	local output_file=$3
	local input_file=$4
	rsvg-convert -w "$width" -h "$height" -o "$output_file" "$input_file"
	oxipng --opt 6 --strip all "$output_file"
}

for size in 128 256; do
	rasterize "$size" "$size" "icon-${size}x${size}.png" "icon.svg"
done

for size in 772x250 1544x500; do
	width=${size%x*}
	height=${size#*x}
	rasterize "$width" "$height" "banner-${size}.png" "banner.svg"
done

rasterize 1280 640 "banner-github.png" "banner-github.svg"
