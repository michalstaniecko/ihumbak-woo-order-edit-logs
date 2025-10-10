# GitHub Actions Workflows

This directory contains GitHub Actions workflows for the iHumBak WooCommerce Order Edit Logs plugin.

## Available Workflows

### 1. Create Plugin ZIP on Main Branch Update

**File:** `create-plugin-zip.yml`

**Trigger:** Automatically runs when code is pushed to the `main` branch

**Purpose:** Creates a distributable ZIP file of the plugin whenever the main branch is updated.

**What it does:**
- Checks out the latest code from the main branch
- Sets up PHP 7.4 and Composer
- Installs production dependencies (without dev dependencies)
- Creates a clean ZIP file excluding:
  - Development files (tests, phpunit.xml.dist, phpcs.xml, etc.)
  - Documentation files (DEVELOPMENT.md, WORKING_PLAN.md, SPECIFICATION.md)
  - Git files (.git, .github, .gitignore)
  - Build configuration files
- Uploads the ZIP as a workflow artifact (available for 30 days)

**Accessing the ZIP:**
1. Go to the Actions tab in GitHub
2. Click on the workflow run
3. Download the artifact from the "Artifacts" section

---

### 2. Create Plugin ZIP (Manual)

**File:** `manual-create-plugin-zip.yml`

**Trigger:** Manually triggered via GitHub Actions UI

**Purpose:** Creates a distributable ZIP file from any branch on-demand.

**What it does:**
- Allows you to specify a branch name (or uses the current branch)
- Same ZIP creation process as the automatic workflow
- Names the ZIP file with the branch name for easy identification
- Uploads the ZIP as a workflow artifact (available for 30 days)

**How to use:**
1. Go to the Actions tab in GitHub
2. Click on "Create Plugin ZIP (Manual)" workflow
3. Click "Run workflow" button
4. (Optional) Enter a branch name, or leave empty to use the default branch
5. Click "Run workflow" to start
6. Wait for the workflow to complete
7. Download the artifact from the completed workflow run

**Branch naming:**
- If you specify a branch like `feature/new-feature`, the ZIP will be named:
  `ihumbak-woo-order-edit-logs-feature-new-feature-YYYYMMDD-HHMMSS.zip`
- Branch slashes are replaced with hyphens for filesystem compatibility

---

## Notes

- Both workflows exclude development and documentation files to create a clean distribution package
- ZIP files are stored as GitHub Actions artifacts for 30 days
- The ZIP filename includes a timestamp for versioning
- Production dependencies are included in the ZIP (vendor directory with --no-dev flag)
- The ZIP structure maintains the plugin directory name for easy WordPress installation

## Requirements

These workflows require:
- GitHub Actions to be enabled for the repository
- No additional secrets or configuration needed
- Standard Ubuntu runner with PHP and Composer support
