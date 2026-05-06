# Threadspace — Git Progress Report & Handoff Guide (2026-05-06)
**Date:** 2026-05-06  
**Repo:** `khimanthonyretiza-cpu/threadspace_group1`  
**Local path:** `C:\xampp\htdocs\threadspace_group1`  
**Primary tool:** Git (VS Code / PowerShell)  
**Goal:** Document what happened in Git today (branches, pushes, auth issues), and provide a cheat sheet for the next developers.

---

## 1) What Happened Today (Git Narrative)

### 1.1 Initial push failure (HTTP 403)
When pushing a branch to the upstream repo, the push failed with:

- `Permission ... denied to cylejovenbombio-collab`
- `fatal: unable to access ... error: 403`

**Root cause:**  
The GitHub account authenticated in the browser/credential manager at that moment did not have write access to the repository.  
**Important reminder:** A **public repo** is only publicly readable; pushing still requires **write permission**.

### 1.2 Push succeeded after re-authentication
A subsequent push attempt succeeded:
- Objects were enumerated and written
- A GitHub PR link was returned
- The branch was created on origin successfully

This indicates that authentication was completed using an identity that *did* have permission, or permissions were updated.

### 1.3 Branch creation workflow
The following branch strategy was used:

- Backup snapshot branches were created and pushed:
  - `backup/2026-05-06`
  - `backup/before-force-push`
- A feature branch was created and pushed:
  - `feature/guest-checkout-and-shop-pages`

### 1.4 Verifying state and history
The repository state was confirmed using:
- `git log --oneline --decorate --graph --all`
- `git branch -vv`
- `git status -sb`

At the time of verification, the branch pointers showed:
- **main** tracked `origin/main`
- backup/feature branches tracked their `origin/*` counterparts
- Working tree was clean after updates were committed/pushed

---

## 2) Current Known Branches (as of today)

> The exact commit hashes may differ later; use `git branch -vv` for the current truth.

Expected remote branches (examples):
- `origin/main`
- `origin/backup/2026-05-06`
- `origin/backup/before-force-push`
- `origin/feature/guest-checkout-and-shop-pages`

### What each branch is for
- **main**  
  Stable branch used for the submission/demo.
- **backup/2026-05-06**  
  A “snapshot” branch before major refactor; safe rollback point.
- **backup/before-force-push**  
  Additional safety snapshot before any forced history rewrite.
- **feature/guest-checkout-and-shop-pages**  
  Work branch for guest checkout + shop/sale/category pages + UI unification.

---

## 3) Recommended Next-Dev Workflow (How to Continue Safely)

### 3.1 Always sync before work
```bash
git checkout main
git pull origin main
```

### 3.2 Make a feature branch for any new work
```bash
git checkout -b feature/<short-description>
```

### 3.3 Commit frequently with meaningful messages
```bash
git status
git add -A
git commit -m "feat: <what changed> (why)"
```

### 3.4 Push and open PR
```bash
git push -u origin feature/<short-description>
```
Then create a PR in GitHub.

### 3.5 Avoid force push unless absolutely necessary
If you must, create a backup branch/tag first:
```bash
git checkout main
git pull
git tag backup-main-before-rewrite-YYYY-MM-DD
git push origin backup-main-before-rewrite-YYYY-MM-DD
```

Use **safer** force:
```bash
git push --force-with-lease origin main
```

---

## 4) Authentication & Permissions Troubleshooting

### 4.1 Public repo ≠ write access
- Anyone can clone a public repo
- Only collaborators/team members with write permission can push

### 4.2 If you see `403` denied
**Fix options**
1. Ensure your GitHub account is a collaborator with Write access
2. Re-authenticate in browser when prompted
3. Clear wrong credentials stored in Windows

### 4.3 Clear GitHub credentials (Windows)
- Open **Credential Manager**
- Remove entries like:
  - `git:https://github.com`
  - `https://github.com`
Then retry `git push`, and complete browser authentication again.

---

## 5) Quick Commands to Inspect the Repo (must-know)

### 5.1 What branch am I on / is it clean?
```bash
git status -sb
```

### 5.2 What changed since last commit?
```bash
git diff
```

### 5.3 What files changed (names only)?
```bash
git diff --name-only
```

### 5.4 See the commit history (graph)
```bash
git log --oneline --decorate --graph --all -n 30
```

### 5.5 See branches and what commit they point to
```bash
git branch -vv
```

### 5.6 Check remote URLs
```bash
git remote -v
```

---

## 6) Git Command Cheat Sheet (Comprehensive)

### A) Setup / Identity
```bash
git --version
git config --global user.name "Your Name"
git config --global user.email "you@example.com"
git config --global --list
```

### B) Initialize & Connect Repo
```bash
git init
git remote add origin https://github.com/OWNER/REPO.git
git remote -v
```

### C) Daily Workflow
**Pull latest:**
```bash
git checkout main
git pull
```

**New feature branch:**
```bash
git checkout -b feature/my-change
```

**Stage + commit:**
```bash
git add -A
git commit -m "feat: describe change"
```

**Push branch:**
```bash
git push -u origin feature/my-change
```

### D) Branch Operations
**List branches:**
```bash
git branch
git branch -vv
```

**Switch branches:**
```bash
git checkout main
git switch main
```

**Delete local branch:**
```bash
git branch -d feature/my-change
```

**Delete remote branch:**
```bash
git push origin --delete feature/my-change
```

### E) Undo / Fix Mistakes
**Unstage a file (keep changes):**
```bash
git restore --staged <file>
```

**Discard local changes to a file (danger):**
```bash
git restore <file>
```

**Reset everything to last commit (danger):**
```bash
git reset --hard
```

**Reset to match remote main (danger):**
```bash
git fetch origin
git reset --hard origin/main
```

### F) Stashing (save work without committing)
```bash
git stash
git stash list
git stash pop
```

### G) Tags (backup snapshots)
```bash
git tag backup-YYYY-MM-DD
git push origin backup-YYYY-MM-DD
```

### H) Force Push (advanced / dangerous)
**Safer force push:**
```bash
git push --force-with-lease origin main
```

**Hard replace main with a branch (danger):**
```bash
git checkout main
git fetch origin
git reset --hard origin/feature/my-change
git push --force-with-lease origin main
```

### I) Comparing branches
```bash
git diff main..feature/my-change
git log main..feature/my-change --oneline
```

### J) Cloning
```bash
git clone https://github.com/OWNER/REPO.git
cd REPO
```

---

## 7) Recommended Branch Naming Conventions
- `feature/<short-kebab-case-description>`
- `fix/<bug-description>`
- `chore/<maintenance-task>`
- `backup/<date-or-purpose>`

Examples:
- `feature/order-success-page`
- `fix/checkout-null-userid`
- `chore/update-readme`
- `backup/2026-05-06`

---

## 8) Handoff Checklist for Incoming Developers
- [ ] Confirm access: can you push to origin without 403?
- [ ] `git pull` on main
- [ ] Confirm latest UI pages exist: `shop.php`, `sale.php`, `category.php`
- [ ] Confirm guest cart is session-based (Option A: placeholders view-only)
- [ ] Create a new feature branch for DB/checkout completion
- [ ] Avoid force push unless agreed by the team; backup tags first

---

## 9) Notes
- If `git log --graph --all` opens in a pager (`less`), press:
  - `q` to quit
- If your terminal repeatedly shows “nothing to commit”, verify:
  - files are saved
  - correct folder opened
  - `.gitignore` isn’t excluding expected files

---
**End of Git progress report.**