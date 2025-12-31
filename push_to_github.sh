#!/bin/bash

# Silent Panel - Push to GitHub Script
# This script pushes all project files to GitHub using the GitHub API

GITHUB_USER="somysam12"
REPO_NAME="10-15-full-workingzip"
GITHUB_TOKEN="$GITHUB_TOKEN"

echo "🚀 Pushing Silent Panel to GitHub..."
echo "Repository: https://github.com/${GITHUB_USER}/${REPO_NAME}"
echo ""

# Check if GITHUB_TOKEN is set
if [ -z "$GITHUB_TOKEN" ]; then
    echo "❌ Error: GITHUB_TOKEN environment variable not set"
    echo "Please provide your GitHub Personal Access Token"
    exit 1
fi

# Create a temporary directory for git operations
TEMP_DIR="/tmp/silent-panel-push-$$"
mkdir -p "$TEMP_DIR"
cd "$TEMP_DIR" || exit 1

# Clone the repository
echo "📥 Cloning repository..."
git clone "https://${GITHUB_USER}:${GITHUB_TOKEN}@github.com/${GITHUB_USER}/${REPO_NAME}.git" . 2>&1 | grep -v "Cloning into" || true

# Configure git
git config user.name "Silent Panel Developer"
git config user.email "dev@silentpanel.local"

# Copy all project files
echo "📂 Copying project files..."
cp -r /silentpanel ./
cp -r /backend ./
cp -r /docs ./
cp -r /*.md ./
cp .gitignore ./gitignore.temp && mv ./gitignore.temp ./.gitignore || true

echo "✅ Files copied"

# Add all files
git add .
git status --short | head -20

# Create commit
echo ""
echo "💾 Creating commit..."
git commit -m "Silent Panel - Complete Android + Backend System

📱 Android App (silentpanel/):
- New: ConfigManager.java, Config.java, Panel.java
- Modified: WebsiteSelectorActivity.java, MainActivity.java
- Resources: logo.png, ic_launcher.png, layouts, colors

🔌 Backend Server (backend/):
- Flask API with admin dashboard
- PostgreSQL integration
- 7+ REST endpoints

📚 Documentation:
- QUICKSTART.md
- INTEGRATION_GUIDE.md
- BACKEND_API_COMPLETE.md
- Project setup guides

✨ Features:
- Dynamic panel management
- Server control & announcements
- Admin dashboard
- Auto-login credentials
- WebView browser
- Fallback mechanism" 2>&1 || echo "Nothing new to commit"

# Push to GitHub
echo ""
echo "🌐 Pushing to GitHub..."
git push -u origin main --force 2>&1 || {
    echo "⚠️  Main branch push failed, trying master..."
    git push -u origin master --force 2>&1 || {
        echo "⚠️  Creating initial commit on main branch..."
        git checkout -b main 2>/dev/null || true
        git push -u origin main --force 2>&1
    }
}

# Check push status
if [ $? -eq 0 ]; then
    echo ""
    echo "✅ SUCCESS! Code pushed to GitHub"
    echo "📍 Repository: https://github.com/${GITHUB_USER}/${REPO_NAME}"
    echo ""
    else
    echo ""
    echo "❌ Push failed. Please try manual push with:"
    echo "   git remote set-url origin https://\${GITHUB_TOKEN}@github.com/${GITHUB_USER}/${REPO_NAME}.git"
    echo "   git push -u origin main --force"
fi

# Cleanup
cd /
rm -rf "$TEMP_DIR"

echo ""
echo "✨ Done!"
