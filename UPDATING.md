# Deploying and updating schools

Each school is its own CWP hosting account with its own database. Code lives in `~/app` (outside the web
root) and `~/public_html` is a symlink to `~/app/public`. Nothing school-specific is stored in git:
`.env`, uploads (`storage/`) and all data are untouched by updates.

## Where changes go

| Change | Where it lives | Affected by template updates? |
| --- | --- | --- |
| Name, logo, colours, content, fees, grades, discounts, mail | Database (Settings, CMS) | No |
| `.env`, uploaded files | Server only, not in git | No |
| Wanted by 2+ schools | Template (`main`), behind a setting | Everyone gets it |
| Wanted by one school (code change) | That school's branch `school/<name>` | Only on merge; conflicts are shown to you |

Never edit code directly on a school's server. Change it on the school's branch, commit, then pull.

## New school

1. DNS: A record for the domain/subdomain to the server.
2. CWP: User Accounts > New Account (own account per school), enable shell access, PHP 8.2+.
3. CWP: MySQL Manager: create an empty database and user.
4. Copy the script to a place the school user can read, then run it as that user:
   `cp scripts/deploy-new-school.sh /tmp/ && chmod 755 /tmp/deploy-new-school.sh`
   SSH in as the school user: `bash /tmp/deploy-new-school.sh` (optional: a tag, e.g. `v1.1.2`).
5. Run AutoSSL in CWP (or set Cloudflare SSL to Full). Then follow the checklist the script prints.

## Update a standard school

```bash
# 1. back up
mysqldump -u DBUSER -p DBNAME > ~/backup_$(date +%F_%H%M).sql
# 2. update
cd ~/app && git fetch --tags && git checkout vX.Y.Z
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear
```

Roll back: `git checkout <previous-tag>`, restore the backup if the migration changed the schema.

## Update a customised school (own branch)

```bash
cd ~/app && git checkout school/NAME
git fetch --tags && git merge vX.Y.Z      # resolve any conflicts, then commit
composer install --no-dev -o && php artisan migrate --force && php artisan optimize:clear
```

Create a school branch from a release tag: `git checkout -b school/NAME vX.Y.Z`. Keep customisations in new
files where possible (new controller/view/route); edits to existing files are what cause conflicts. If a
customisation needs a database change, give it its own migration with a later timestamp.
Try the merge on a scratch copy first when the change is large.

## Releasing the template

```bash
cd /home/raha/schoolmanager
git add -A && git commit -m "..." && git tag vX.Y.Z && git push origin main --tags
```

Chain with `&&` so a failed commit never creates the tag. Migrations must be additive (new tables, or
nullable/defaulted columns) so existing schools update safely. Test every release on a fresh install
(`migrate` + `school:install` in a scratch database) before deploying it.

## Troubleshooting

- 500 error right after deploy: group-writable PHP files (suPHP). Run
  `find ~/app -type f -name '*.php' -exec chmod 644 {} +` and `find ~/app -type d -exec chmod 755 {} +`.
- Uploads over 100 MB fail behind Cloudflare (its body limit).
- SSH to the server must use the server IP, not a Cloudflare-proxied domain.
- `.env` changes not applied: `php artisan optimize:clear`.
