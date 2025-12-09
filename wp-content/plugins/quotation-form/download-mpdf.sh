#!/bin/bash

# Download and install mPDF library
# Run this script from the plugin directory

echo "📥 Downloading mPDF v8.2.4..."

# Create vendor/mpdf directory
mkdir -p vendor/mpdf

# Download mPDF
cd vendor/mpdf
curl -L https://github.com/mpdf/mpdf/releases/download/v8.2.4/mpdf.zip -o mpdf.zip

# Extract
echo "📦 Extracting mPDF..."
unzip -q mpdf.zip -d mpdf

# Remove zip file
rm mpdf.zip

# Verify installation
if [ -f "mpdf/vendor/autoload.php" ]; then
    echo "✅ mPDF installed successfully!"
    echo "📁 Location: vendor/mpdf/mpdf/"
    echo ""
    echo "Next steps:"
    echo "1. git add vendor/mpdf/"
    echo "2. git commit -m 'Add mPDF library'"
    echo "3. git push"
else
    echo "❌ Installation failed. Please check the structure."
    exit 1
fi
