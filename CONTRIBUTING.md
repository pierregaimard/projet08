# How to contribute

Welcome in the Todo&Co company. This document explains how to contribute to this amazing
project.

## Repository structure and workflow
The repository structure is based on the workflow
[GitFlow](https://www.atlassian.com/fr/git/tutorials/comparing-workflows/gitflow-workflow#:~:text=Le%20workflow%20Gitflow%20global%20est,cr%C3%A9%C3%A9es%20%C3%A0%20partir%20de%20develop).
It contains a `master` branch, a `develop` branch, the different `feature/*` branches, and
some `release/*` branches. It can also contain temporarily a `hotfix` branch.

### The master branch
This branch contains the project history. Every commit is tagged with a version number that
correspond to the different project releases. That represents the different production versions.

### The develop branch
This is the main branch in the project. It contains the most advanced project version.
It is from this branch that the `release` and `feature` branches, are created.

### The feature/* branches
When a new `feature` or a modification has to be done, we create a new `feature` branch from
the `develop` one. Several `feature` branches can be created in parallel, so the development
team can work on different features at the same time. When the new feature is done, his branch
is merged into `develop`.

### The release/* branches
When some new features are done, and we have to add it into production, we create a new `release`
branch from `develop` with a new version number. When a new `release` version is created, no more
feature should be added to it. this branch should be dedicated only to bug fixing, testing, code
cleanup, and documentation. When the new release is ready, it can be merged into `master` with a
new tag version that represent a new production version.  
This `release` branch is merged again into `develop`, so it stays up to date.

### The hotfix branch
A `hotfix` branch is used to fix a bug. It is created from `master`. When the bug has been fixed,
the branch is merged again into `master` with a new tag version.  
After that, the `hotfix` branch is merged into `develop`, so this one stays up to date.
Finally, `hotfix` it is destroyed.

## How to work with this repository ?

### Add a new feature

1. Add a new issue to the repository that describe the feature or the code modification.
1. Create a new feature branch
1. Build a first application performance profile with Blackfire
1. Clone the project on your local machine
1. Develop the new feature branch
1. Push your commits in the `origin/feature/*` branch.
1. Add a pull request and check the different code controls (Travis-Ci for tests and code
   coverage and CodeClimate/SymfonyInsight for code quality)
1. Fix the issues reported on the analysis tools if needed
1. Build a new application performance profile with Blackfire
1. Compare to first profile and fix performance issues if needed
1. Ask the lead developer for code review
1. Improve your code if needed 
1. Merge the branch into `develop`

### Add a new release

1. Create a release branch with a new tag version (e.g. `release/v1.2.0`)
1. Clone the repository on your locale machine
1. Verify and clean the code if needed, check the tests and add missing documentation
1. Push your commits into `origin/release/vx.x.x`
1. Create a pull request 
1. Verify and check the code controls (Travis-Ci, CodeClimate and SymfonyInsight)
1. Fix the issues reported on the analysis tools if needed
1. Ask the lead developer for code review
1. Improve your code if needed
1. Merge the branch into `master` with the tag version (vx.x.x)
1. Create a new release with changelog description
1. Merge the release branch into `develop` to keep it up to date

### Fix a bug in production

1. Add a new issue to the repository that describe the bug
1. Create a `hotfix` branch
1. Build a first application performance profile with Blackfire
1. Clone the repository on your locale machine
1. Fix the bug
1. Write tests to ensure that the bug will not reoccur
1. Push your commits into `origin/hotfix` branch
1. Create a pull request
1. Verify and check the code controls (Travis-Ci, CodeClimate and SymfonyInsight)
1. Fix the issues reported on the analysis tools if needed
1. Build a new application performance profile with Blackfire
1. Compare to first profile and fix performance issues if needed
1. Ask the lead developer for code review
1. Improve your code if needed
1. Merge the branch into `master` with a new tag version (vx.x.x)
1. Merge the branch into `develop` to keep it up to date.
1. Delete the `hotfix` branch

## How to write the code

### Unit and Functional Tests

Units and functional tests must be written for every new feature or code improvement. These tests
should cover all possible paths of the code execution to ensure proper operation and adherence to
specifications.  
Also, a test should be added when a bug occurs to prevent it from happening again.

### Code quality

The code style must respect the PSR-12 standard that include the PSR-1 and PSR-2.  
You should take time to choose good variables names to ensure the readability of the code for
other developers.  
You should also add comments when it is necessary. However, these must be short, clear and not
too numerous to prevent the code from becoming unreadable.  
Also, you should respect the [Symfony framework best practices](https://symfony.com/doc/4.4/best_practices.html)
and the [Symfony coding standard](https://symfony.com/doc/4.4/contributing/code/standards.html).

The repository is also connected to CodeClimate and SymfonyInsight tools to guarantee its quality
and maintainability. All reported issues must be fixed before you merge a feature in the
`develop` branch.

### Performance

Before adding a new feature or application improvement, you should build a profile with Blackfire.
At the end of the development, you should build a new profile and fix the issues if needed.

## Continuous integration

To make the control easier, the repository is connected to the continuous integration tool
Travis-CI. When a commit is pushed in a branch `origin/feature/*`, a build starts in Travis-CI.
His job is to:
- Execute units and functional tests.
- Send the code-coverage report to CodeClimate
- Send a notification to a dedicated Slack canal to display build result.

And, that's it !  
Thanks!!
