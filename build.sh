#!/usr/bin/env bash
CWD_BASENAME=${PWD##*/}
CWD_BASEDIR=${PWD}

if [ ! -d "${CWD_BASEDIR}/vendor/" ]; then
  composer install --no-dev --prefer-dist
  composer -o dump-autoload
  find vendor/ -type d -exec cp index.php {} \;
  cd ..
fi

cd ${CWD_BASEDIR}/views/js/src/
if [ ! -d "${CWD_BASEDIR}/views/js/src/node_modules/" ]; then
  npm i
fi
rm -rf ${CWD_BASEDIR}/views/js/dist/
NODE_ENV=production webpack --mode production
cp ${CWD_BASEDIR}/views/js/src/index.php ${CWD_BASEDIR}/views/js/dist/index.php
cd ${CWD_BASEDIR}

FILES=("logo.gif")
FILES+=("logo.png")
FILES+=("LICENSE")
FILES+=("${CWD_BASENAME}.php")
FILES+=("index.php")
FILES+=("controllers/**")
FILES+=("sql/**")
FILES+=("translations/**")
FILES+=("upgrade/**")
FILES+=("vendor/**")
FILES+=("views/index.php")
FILES+=("views/css/**")
FILES+=("views/img/**")
FILES+=("views/js/index.php")
FILES+=("views/js/jquery.sortable.js")
FILES+=("views/js/sweetalert-2.1.0.min.js")
FILES+=("views/js/dist/*.min.js")
FILES+=("views/js/dist/index.php")
FILES+=("views/templates/**")

MODULE_VERSION="$(sed -ne "s/\\\$this->version *= *['\"]\([^'\"]*\)['\"] *;.*/\1/p" ${CWD_BASENAME}.php)"
MODULE_VERSION=${MODULE_VERSION//[[:space:]]}
ZIP_FILE="${CWD_BASENAME}/${CWD_BASENAME}-v${MODULE_VERSION}.zip"

echo "Going to zip ${CWD_BASENAME} version ${MODULE_VERSION}"

rm ${ZIP_FILE}
cd ..
for E in "${FILES[@]}"; do
  find ${CWD_BASENAME}/${E}  -type f -exec zip -9 ${ZIP_FILE} {} \;
done
