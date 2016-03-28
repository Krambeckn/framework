git subsplit.sh init git@github.com:netforcews/framework.git
git subsplit.sh update

git subsplit.sh publish --heads="master" src/Support:git@github.com:netforcews/support.git

rd /S /Q .subsplit