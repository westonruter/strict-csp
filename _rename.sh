#!/bin/bash
# This script was first developed with Gemini 2.5 Pro (Preview) with the prompt:
# > Create a bash script that takes a single argument like "Foo Bar" and then does a search and replace for the files in
# > the directory, to replace "WP Plugin Template" with "Foo Bar", `wp-plugin-template` with `foo-bar` (e.g. slugs), and
# > `WPPluginTemplate` with `FooBar` (e.g. PHP namespaces). Finally, the bash script should do `git rm` on itself.

# Exit immediately if a command exits with a non-zero status.
set -e
# Treat unset variables as an error when substituting.
set -u
# Prevent errors in a pipeline from being masked.
set -o pipefail

# --- Configuration ---
# The strings to search for
SEARCH_ORIGINAL="WP Plugin Template"
SEARCH_SLUG="wp-plugin-template"
SEARCH_PASCAL="WPPluginTemplate"

# --- Script Logic ---

# Check if an argument is provided
if [ -z "${1:-}" ]; then
  echo "Usage: $0 \"Your Plugin Name\""
  echo "Example: $0 \"Awesome Plugin\""
  exit 1
fi

PLUGIN_NAME_ARG="$1"
SCRIPT_NAME=$(basename "$0")

echo "Starting the renaming process..."
echo "Plugin Name Argument: \"$PLUGIN_NAME_ARG\""
echo "Script name: \"$SCRIPT_NAME\" (will be excluded from changes and then removed)"

# 1. Original Name
# Example: "Foo Bar"
REPLACE_ORIGINAL="$PLUGIN_NAME_ARG"
echo "Replacing \"$SEARCH_ORIGINAL\" with \"$REPLACE_ORIGINAL\""

# 2. Slug Name (lowercase, spaces to hyphens)
# Example: "foo-bar"
REPLACE_SLUG=$(echo "$PLUGIN_NAME_ARG" | tr '[:upper:]' '[:lower:]' | sed 's/ /-/g' | sed 's/[^a-z0-9-]//g')
echo "Replacing \"$SEARCH_SLUG\" with \"$REPLACE_SLUG\""

# 3. PascalCase Name (remove spaces, capitalize each word - assuming input is Title Case, with words after hyphens also capitalized before the hyphen is removed)
# Example: "FooBar"
# This simple version just removes spaces. For more complex PascalCase conversion from various inputs,
# a more sophisticated approach might be needed, but this matches the user's example.
REPLACE_PASCAL=$(echo "$PLUGIN_NAME_ARG" | sed -E 's/(^|[- ])([a-z])/\U\2/g; s/[- ]//g')
echo "Replacing \"$SEARCH_PASCAL\" with \"$REPLACE_PASCAL\""

echo ""
echo "Searching for files to modify (excluding .git directory and this script)..."

# Find files and perform replacements
# - We use `find` to locate all files (-type f).
# - Exclude the .git directory (`-not -path "./.git/*"`).
# - Exclude the script itself (`-not -name "$SCRIPT_NAME"`).
# - `-print0` and `xargs -0` handle filenames with spaces or special characters safely.
# - `sed -i ''` performs in-place editing (the `''` is for macOS compatibility; Linux sed might not need it).
# - We use '#' as a delimiter for sed to avoid issues if names contain '/'.

# Create a temporary file to list files that will be changed
TEMP_FILE_LIST=$(mktemp)

find . -type f -not -path "./.git/*" -not -name "$SCRIPT_NAME" -print0 > "$TEMP_FILE_LIST"

if [ ! -s "$TEMP_FILE_LIST" ] && [ ! -z "$(head -n 1 "$TEMP_FILE_LIST")" ]; then
    echo "No files found to process (excluding .git and script itself)."
else
    echo "Processing the following files:"
    # Display files that will be processed (for transparency, read them back from the null-delimited file)
    # Using a loop to read null-delimited filenames
    while IFS= read -r -d $'\0' file; do
        echo "  - $file"
        sed -i'' \
            -e "s#${SEARCH_ORIGINAL}#${REPLACE_ORIGINAL}#g" \
            -e "s#${SEARCH_SLUG}#${REPLACE_SLUG}#g" \
            -e "s#${SEARCH_PASCAL}#${REPLACE_PASCAL}#g" "$file"
    done < "$TEMP_FILE_LIST"
fi

if [ -e "$SEARCH_SLUG.php" ]; then
    echo "Renaming $SEARCH_SLUG.php to $REPLACE_SLUG.php"
    git mv "$SEARCH_SLUG.php" "$REPLACE_SLUG.php"
fi

# Clean up the temporary file list
rm "$TEMP_FILE_LIST"

# Remove the script itself using git rm
echo ""
echo "Removing the script '$SCRIPT_NAME' from git..."
git rm "$0"

echo ""
echo "Process finished!"
echo "Make sure to review the changes and commit them to your repository."
