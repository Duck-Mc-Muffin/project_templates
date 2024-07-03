# Templates

This repository is mainly for me to store boilerplate code to start projects and share my configuration.

Every top level directory corresponds to a project template and provides it's own README's with instructions.

## "Copy" a template subdirectory with git

> NOTE: Cloning into a temporary folder on the same level and moving it with `mv tmp/<source> <target>` makes sure to also move "hidden" files and directories.

```bash
git clone --no-checkout --depth=1 https://github.com/Duck-Mc-Muffin/project_templates.git tmp

cd tmp
git sparse-checkout set --no-cone aspnet_nvim
git checkout main
rm -r .git

cd ..
mv tmp/aspnet_nvim homepage
rmdir tmp

cd homepage
```
