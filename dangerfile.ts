import {danger, warn, fail, message} from 'danger';

// GitHub-specific checks
if (danger.github) {
	if (!danger.github.pr.title) {
		fail(`Ope! This PR is missing a title. Can you add a relevant title?`);
	}

	// No PR is too small to include a description of why you made a change
	if (danger.github.pr.body.length < 10 || !danger.github.pr.body.includes('## Summary')) {
		fail('Please include a description of your PR changes so our future selves are thankful of our past selves. 😃');
	}

	if (!danger.github.pr.title.match(/^(`.*?`|Tooling|Formatting): (Added|Fixed|Updated|Removed|Improved|Deprecated)/g)) {
		fail(`Pull request title does match the correct format. The Pull Request title should match our Snippet Library Pull Request Title Guidelines in Notion.`)
	}

	if (!danger.github.pr.title.match(/[.!]{1}$/g)) {
		fail(`Pull request title needs to end in a period or exclamation.`)
	}

	// Reminder to add review requests.
	if (!danger.github.requested_reviewers.users.length && !danger.github.requested_reviewers.teams.length) {
		warn(`When ready, don't forget to request reviews on this pull request from your fellow wizards.`)
	}
}

// Enforce commit message guidelines
danger.git.commits.forEach(commit => {
	if (!commit.message.match(/^(`.*?`|Tooling|Formatting): (Added|Fixed|Updated|Removed|Improved|Deprecated)/g)) {
		fail(`Commit message '${commit.message}' does match the correct format. See our Snippet Library Commit Messages Guidelines in Notion.`)
	}

	if (!commit.message.match(/[.!]{1}$/g)) {
		fail(`Commit message '${commit.message}' needs to end in a period or exclamation.`)
	}
})

// Various checks on new snippet files
if (danger.git.created_files.length) {
	danger.git.created_files.forEach((createdFile) => {
		if (!createdFile.match(/^(gravity-forms|expermental|gp-*.?|wc-gf-product-addons|gravity-flow)\/(.*?).(php|js)$/)) {
			return;
		}

		danger.git.diffForFile(createdFile).then(diff => {
			const hasGFSnippetHeader = diff.added.match(/^\+\s+\*\s+Gravity Wiz \/\/ Gravity Forms \/\/ (.*)$/m);
			const hasPerkSnippetHeader = diff.added.match(/^\+\s+\*\s+Gravity Perks \/\/ (.*) \/\/ (.*)$/m);
			const hasLoomVideo = diff.added.match(/loom.com\/share/m);

			if (!hasGFSnippetHeader && !hasPerkSnippetHeader) {
				fail(createdFile + ": it looks like this file does not have the appropriate snippet header.")
			}

			if (hasLoomVideo) {
				message(`A new snippet with a Loom video? Magical! 📹`)
			}

			message(`Merlin would give this scroll the highest of praises. Cheers for adding this new snippet to the library! 🪄`)
		})
	})
}

// Various checks on modified snippet files
if (danger.git.modified_files.length) {
	danger.git.modified_files.forEach((modifiedFile) => {
		if (!modifiedFile.match(/^(gravity-forms|expermental|gp-*.?|wc-gf-product-addons|gravity-flow)\/(.*?).(php|js)$/)) {
			return;
		}

		danger.git.diffForFile(modifiedFile).then(diff => {
			const hasLoomVideo = diff.added.match(/loom.com\/share/m);

			if (hasLoomVideo) {
				message(`Nice work adding a Loom video! 📹`)
			}
		})
	})
}

// High praises!
const dangerfile = danger.git.fileMatch('dangerfile.ts')
const gitHubActions = danger.git.fileMatch('.github/workflows/**/*.yml')
const phpcsRuleset = danger.git.fileMatch('phpcs.xml')

if (dangerfile.modified) {
	message(`Well this is meta. Thanks for improving the Dangerfile! ❤️`)
}

if (gitHubActions.edited) {
	message(`Look at you go, you GitHub Actions wizard! 🧙‍️`)
}

if (phpcsRuleset.modified) {
	message(`A wizard who updates standards has high standards. Cheers for updating our PHPCS ruleset!‍️ 🥂`)
}
