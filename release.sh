#!/bin/bash

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
    exit 1
}

print_info() {
    echo -e "${YELLOW}→ $1${NC}"
}

print_header() {
    echo -e "${BLUE}=== $1 ===${NC}"
}

# Show usage
usage() {
    echo "Usage: $0 [major|minor|patch]"
    echo "  major - Bump major version (1.0.0 -> 2.0.0)"
    echo "  minor - Bump minor version (1.0.0 -> 1.1.0)"
    echo "  patch - Bump patch version (1.0.0 -> 1.0.1) [default]"
    exit 1
}

# Parse arguments
BUMP_TYPE=${1:-patch}
if [[ ! "$BUMP_TYPE" =~ ^(major|minor|patch)$ ]]; then
    usage
fi

print_header "Server Manager Release Script"

# Check if we're in the right directory
if [ ! -f "composer.json" ] || [ ! -f "package.json" ]; then
    print_error "This script must be run from the package root directory"
fi

# Check for uncommitted changes
if ! git diff-index --quiet HEAD --; then
    print_error "You have uncommitted changes. Please commit or stash them first."
fi

# Ensure we're on main branch
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$CURRENT_BRANCH" != "main" ]; then
    print_error "You must be on the main branch to create a release. Current branch: $CURRENT_BRANCH"
fi

# Pull latest changes
print_info "Pulling latest changes..."
git pull origin main || print_error "Failed to pull latest changes"

# Get current version
CURRENT_VERSION=$(git describe --tags --abbrev=0 2>/dev/null || echo "v0.0.0")
print_info "Current version: $CURRENT_VERSION"

# Parse version components
VERSION_WITHOUT_V=${CURRENT_VERSION#v}
IFS='.' read -r -a VERSION_PARTS <<< "$VERSION_WITHOUT_V"
MAJOR=${VERSION_PARTS[0]:-0}
MINOR=${VERSION_PARTS[1]:-0}
PATCH=${VERSION_PARTS[2]:-0}

# Calculate new version based on bump type
case $BUMP_TYPE in
    major)
        NEW_VERSION="v$((MAJOR + 1)).0.0"
        ;;
    minor)
        NEW_VERSION="v${MAJOR}.$((MINOR + 1)).0"
        ;;
    patch)
        NEW_VERSION="v${MAJOR}.${MINOR}.$((PATCH + 1))"
        ;;
esac

print_info "Bumping $BUMP_TYPE version: $CURRENT_VERSION → $NEW_VERSION"

# Confirm with user
echo ""
read -p "Continue with release $NEW_VERSION? (y/N) " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    print_error "Release cancelled"
fi

print_header "Running Quality Checks"

# Run tests
print_info "Running tests..."
if composer test > /dev/null 2>&1; then
    print_success "Tests passed"
else
    print_error "Tests failed"
fi

# Run static analysis
print_info "Running PHPStan..."
if ./vendor/bin/phpstan analyse --memory-limit=2G > /dev/null 2>&1; then
    print_success "PHPStan passed"
else
    print_error "PHPStan failed"
fi

# Format code
print_info "Running code formatter..."
if composer format > /dev/null 2>&1; then
    print_success "Code formatted"
else
    print_error "Code formatting failed"
fi

print_header "Building Assets"

# Install npm dependencies
print_info "Installing npm dependencies..."
if npm install > /dev/null 2>&1; then
    print_success "npm dependencies installed"
else
    print_error "npm install failed"
fi

# Build assets
print_info "Building frontend assets..."
if npm run build > /dev/null 2>&1; then
    print_success "Assets built"
else
    print_error "Asset build failed"
fi

print_header "Preparing Release"

# Add built assets to git
print_info "Staging built assets..."
git add -f dist/
git add .

# Check if there are changes to commit
if ! git diff --cached --quiet; then
    print_info "Committing changes..."
    git commit -m "chore: build assets for release $NEW_VERSION" || print_error "Commit failed"
    print_success "Changes committed"
fi

# Create and push tag
print_info "Creating tag $NEW_VERSION..."
git tag -a "$NEW_VERSION" -m "Release $NEW_VERSION

- Built assets for distribution
- Ready for production use"
print_success "Tag created"

print_header "Publishing Release"

# Push to origin
print_info "Pushing to GitHub..."
git push origin main || print_error "Failed to push to main branch"
git push origin "$NEW_VERSION" || print_error "Failed to push tag"
print_success "Pushed to GitHub"

# Success message
echo ""
print_header "Release Complete! 🎉"
print_success "Version $NEW_VERSION has been released"
echo ""
echo "Next steps:"
echo "1. ⏳ Wait for GitHub Actions to complete"
echo "2. 📝 Create a GitHub release at: https://github.com/metacomet-technologies/server-manager/releases/new"
echo "3. 📦 Verify on Packagist: https://packagist.org/packages/metacomet-technologies/server-manager"
echo "4. 🧪 Test installation: composer require metacomet-technologies/server-manager"
echo ""