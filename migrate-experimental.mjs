#!/usr/bin/env node

import fs from 'fs/promises';
import path from 'path';
import { execSync } from 'child_process';

const EXPERIMENTAL_DIR = 'experimental';

// Map prefixes to their target directories
const PREFIX_MAP = {
  // Gravity Forms Core & Related
  'gf': 'gravity-forms',
  'gflow': 'gravity-flow',
  'gfoai': 'gravityforms-openai',

  // Gravity Connect
  'gca': 'gc-airtable',
  'gcgs': 'gc-google-sheets',
  'gcn': 'gc-notion',
  'gcoai': 'gc-openai',

  // Gravity Perks
  'gpaa': 'gp-address-autocomplete',
  'gpac': 'gp-advanced-calculations',
  'gpapf': 'gp-advanced-phone-field',
  'gpasc': 'gp-advanced-save-and-continue',
  'gpadvs': 'gp-advanced-select',
  'gpalf': 'gp-auto-list-field',
  'gpal': 'gp-auto-login',
  'gpbua': 'gp-better-user-activation',
  'gpb': 'gp-blocklist',
  'gpcld': 'gp-conditional-logic-dates',
  'gpcp': 'gp-conditional-pricing',
  'gpcc': 'gp-copy-cat',
  'gpdtc': 'gp-date-time-calculator',
  'gpdec': 'gp-easy-passthrough',
  'gpep': 'gp-easy-passthrough',
  'gpepee': 'gp-easy-passthrough',
  'gpecf': 'gp-ecommerce-fields',
  'gpeu': 'gp-email-users',
  'gpeb': 'gp-entry-blocks',
  'gpfr': 'gp-file-renamer',
  'gpfup': 'gp-file-upload-pro',
  'gpgs': 'gp-google-sheets',
  'gpi': 'gp-inventory',
  'gplcb': 'gp-limit-checkboxes',
  'gplc': 'gp-limit-choices',
  'gpld': 'gp-limit-dates',
  'gpls': 'gp-limit-submissions',
  'gplp': 'gp-live-preview',
  'gpml': 'gp-media-library',
  'gpmpn': 'gp-multi-page-navigation',
  'gpnf': 'gp-nested-forms',
  'gpns': 'gp-notification-scheduler',
  'gppt': 'gp-page-transitions',
  'gpppw': 'gp-pay-per-word',
  'gppa': 'gp-populate-anything',
  'gppcmt': 'gp-post-content-merge-tags',
  'gpps': 'gp-preview-submission',
  'gppr': 'gp-price-range',
  'gpqr': 'gp-qr-code',
  'gpro': 'gp-read-only',
  'gprf': 'gp-reload-form',
  'gpuid': 'gp-unique-id',
  'gpwc': 'gp-word-count',

  // Gravity Shop
  'gspc': 'gs-product-configurator',
  'wcgfpa': 'wc-gf-product-addons',

  // General Gravity Wiz
  'gw': 'gravity-forms'
};

// Special cases that don't follow prefix pattern
const SPECIAL_CASES = {
  'gp-hide-perks-from-plugins-page.php': 'gravity-forms',
  'gp-update-perks-tab-title.php': 'gravity-forms'
};

async function getTargetDir(filename) {
  // Check special cases first
  if (SPECIAL_CASES[filename]) {
    return SPECIAL_CASES[filename];
  }

  // Get the prefix from the filename
  const prefix = Object.keys(PREFIX_MAP)
    .sort((a, b) => b.length - a.length) // Sort by length descending to match longest prefix first
    .find(prefix => filename.startsWith(prefix));

  if (!prefix) {
    throw new Error(`No matching prefix found for ${filename}`);
  }
  return PREFIX_MAP[prefix];
}

async function readFileContent(filepath) {
  const content = await fs.readFile(filepath, 'utf8');
  return content;
}

async function updateHeaderWithExperimental(content, filename) {
  const lines = content.split('\n');
  const isPHP = filename.endsWith('.php');
  let headerStart = -1;
  let headerEnd = -1;

  // Find the header block
  for (let i = 0; i < lines.length; i++) {
    if (lines[i].includes('/**')) {
      headerStart = i;
    } else if (headerStart !== -1 && lines[i].includes('*/')) {
      headerEnd = i;
      break;
    }
  }

  // If no header found, create a new one
  if (headerStart === -1) {
    const prefix = isPHP ? '<?php\n' : '';
    return `${prefix}/**
 * Experimental Snippet 🧪
 */
${content.replace(/^<\?php\n?/, '')}`;  // Remove any existing PHP tag
  }

  // Extract header content
  const headerLines = lines.slice(headerStart, headerEnd + 1);
  const beforeHeader = lines.slice(0, headerStart);
  const afterHeader = lines.slice(headerEnd + 1);

  // Remove any existing experimental flag
  const cleanedHeaderLines = headerLines.filter(line => !line.includes('Experimental Snippet'));

  // Find title and URL lines
  const titleLineIndex = cleanedHeaderLines.findIndex(line => {
    const trimmed = line.trim();
    return trimmed.startsWith('* Gravity Wiz //') || trimmed.startsWith('* Gravity Perks //');
  });

  // Find URL line
  const urlLineIndex = cleanedHeaderLines.findIndex(line =>
    line.includes('https://') || line.includes('http://')
  );

  // Determine where to insert the experimental flag
  let insertIndex;
  if (urlLineIndex !== -1) {
    // Insert after URL
    insertIndex = urlLineIndex;
  } else if (titleLineIndex !== -1) {
    // Insert after title
    insertIndex = titleLineIndex;
  } else {
    // No title found, insert after comment start
    insertIndex = 0;
  }

  // Add experimental flag with appropriate blank lines
  if (insertIndex === 0) {
    cleanedHeaderLines.splice(1, 0, ' * Experimental Snippet 🧪');
  } else {
    // Always add blank line before experimental flag
    cleanedHeaderLines.splice(insertIndex + 1, 0, ' *', ' * Experimental Snippet 🧪');
  }

  // Remove any duplicate blank lines
  for (let i = cleanedHeaderLines.length - 2; i >= 0; i--) {
    if (cleanedHeaderLines[i].trim() === '*' && cleanedHeaderLines[i + 1].trim() === '*') {
      cleanedHeaderLines.splice(i, 1);
    }
  }

  // Reconstruct the file
  let newContent = '';

  // Add PHP tag if needed
  if (isPHP) {
    newContent += '<?php\n';
  }

  // Add any content that was before the header (except PHP tag)
  const beforeHeaderContent = beforeHeader
    .filter(line => !line.includes('<?php'))
    .join('\n');
  if (beforeHeaderContent.trim()) {
    newContent += beforeHeaderContent + '\n';
  }

  // Add the modified header
  newContent += cleanedHeaderLines.join('\n') + '\n';

  // Add the rest of the file
  if (afterHeader.length) {
    newContent += afterHeader.join('\n');
  }

  return newContent;
}

async function createDeprecationNotice(newPath) {
  const extension = path.extname(newPath);
  const prefix = extension === '.php' ? '<?php\n' : '';

  return `${prefix}/**
 * We're no longer using the experimental folder for experimental snippets. 🚧
 * You can now find the snippet here:
 * https://github.com/gravitywiz/snippet-library/blob/master/${newPath}
 */`;
}

async function fileExists(filepath) {
  try {
    await fs.access(filepath);
    return true;
  } catch {
    return false;
  }
}

async function processFile(filename) {
  if (filename === 'readme.md' || filename === '.DS_Store') {
    return {
      filename,
      targetDir: '',
      status: 'Skipped',
      error: null
    };
  }

  const sourcePath = path.join(EXPERIMENTAL_DIR, filename);
  const targetDir = await getTargetDir(filename);
  const targetPath = path.join(targetDir, filename);

  console.log(`Processing ${filename}...`);
  console.log(`Target directory: ${targetDir}`);

  try {
    // Read original content
    const content = await readFileContent(sourcePath);

    // Check if already migrated
    if (content.includes("We're no longer using the experimental folder")) {
      console.log(`${filename} already migrated, skipping...`);
      return {
        filename,
        targetDir,
        status: 'Already Migrated',
        error: null
      };
    }

    // Create target directory if it doesn't exist
    await fs.mkdir(targetDir, { recursive: true });

    // Check if target file already exists
    if (await fileExists(targetPath)) {
      console.log(`${targetPath} already exists, skipping...`);
      return {
        filename,
        targetDir,
        status: 'Target Exists',
        error: null
      };
    }

    // Update content with experimental flag
    const updatedContent = await updateHeaderWithExperimental(content, filename);

    // Move the file and update its content in a single commit
    execSync(`git mv "${sourcePath}" "${targetPath}"`);
    await fs.writeFile(targetPath, updatedContent);
    execSync(`git add "${targetPath}"`);
    execSync(`git commit -m '\`${filename}\`: Migrated from experimental folder'`);

    // Create deprecation notice in original location (without committing)
    const deprecationNotice = await createDeprecationNotice(targetPath);
    await fs.writeFile(sourcePath, deprecationNotice);
    console.log(`Updated ${sourcePath} with deprecation notice`);

    return {
      filename,
      targetDir,
      status: 'Migrated',
      error: null
    };
  } catch (err) {
    console.error(`Error processing ${filename}:`, err);
    return {
      filename,
      targetDir,
      status: 'Error',
      error: err.message
    };
  }
}

function printResults(results) {
  // Count statistics
  const stats = {
    migrated: results.filter(r => r.status === 'Migrated').length,
    alreadyMigrated: results.filter(r => r.status === 'Already Migrated').length,
    targetExists: results.filter(r => r.status === 'Target Exists').length,
    errors: results.filter(r => r.status === 'Error').length,
    skipped: results.filter(r => r.status === 'Skipped').length,
    total: results.length
  };

  // Print summary
  console.log('\nMigration Summary:');
  console.log('=================');
  console.log(`Total files: ${stats.total}`);
  console.log(`✅ Migrated: ${stats.migrated}`);
  console.log(`⏭️  Already migrated: ${stats.alreadyMigrated}`);
  console.log(`⚠️  Target exists: ${stats.targetExists}`);
  console.log(`❌ Errors: ${stats.errors}`);
  console.log(`⏩ Skipped: ${stats.skipped}`);

  // Print table header
  console.log('\nDetailed Results:');
  console.log('| File | Target Directory | Status | Error |');
  console.log('|------|-----------------|---------|--------|');

  // Print each result
  results.forEach(result => {
    const status = {
      'Migrated': '✅ Migrated',
      'Already Migrated': '⏭️  Already Migrated',
      'Target Exists': '⚠️  Target Exists',
      'Error': '❌ Error',
      'Skipped': '⏩ Skipped'
    }[result.status];

    console.log(`| \`${result.filename}\` | ${result.targetDir || '-'} | ${status} | ${result.error || '-'} |`);
  });
}

async function main() {
  try {
    const files = await fs.readdir(EXPERIMENTAL_DIR);
    const results = [];

    for (const file of files) {
      const result = await processFile(file);
      results.push(result);
    }

    printResults(results);
  } catch (err) {
    console.error('Error:', err);
    process.exit(1);
  }
}

main();
