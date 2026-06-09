# Installer les dépendances
composer install
npm install

# Configurer la base de données dans .env
DB_CONNECTION=pgsql
DB_DATABASE=biloki
DB_USERNAME=...
DB_PASSWORD=...

# Lancer les migrations
php artisan migrate

# Compiler les assets
npm run build

# Créer un compte admin
php artisan tinker
>>> \App\Models\User::create(['name'=>'David','email'=>'admin@biloki.mg','password'=>bcrypt('password'),'is_admin'=>true])
