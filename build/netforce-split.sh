git subsplit init git@github.com:netforcews/framework.git
git subsplit update

git subsplit publish --heads="master" src/Support:git@github.com:netforcews/support.git
git subsplit publish --heads="master" src/Validation:git@github.com:netforcews/validation.git
git subsplit publish --heads="master" src/Database:git@github.com:netforcews/database.git
git subsplit publish --heads="master" src/IO:git@github.com:netforcews/io.git
git subsplit publish --heads="master" src/Http:git@github.com:netforcews/http.git
git subsplit publish --heads="master" src/Clients:git@github.com:netforcews/clients.git

rm -rf .subsplit/