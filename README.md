# Instalation

```bash
composer installd
php artisan config:cache # Maybe optional
php artisan migrate # Create database tables
php artisan db:seed # Populate the database
php artisan storage:link # Create a storage link to fix images
php artisan passport:keys # Generate keys, one time only
php artisan passport:install # Generate passport client keys
```
