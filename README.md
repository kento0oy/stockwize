🚀 Getting Started

Follow these steps to clone and run the project locally.

1. Clone the Repository
```bash
git clone https://github.com/kento0oy/stockwize.git
cd stockwize
```

2. Install Dependencies
```bash
composer install
```

### 3. Set Up Environment File
```bash
cp .env.example .env
```

### 4. Generate Application Key
```bash
php artisan key:generate
```

### 5. Configure Database
Open the `.env` file and update the database section:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=stockwize
DB_USERNAME=root
DB_PASSWORD=
```

6. Run Database Migrations & Seeders
```bash
php artisan migrate --seed
```

This will create all tables and insert the initial data.

7. Start the Development Server
```bash
php artisan serve
```

Now visit `http://127.0.0.1:8000` in your browser.

---

**You're all set!** 🎉

If you encounter any issues, make sure:
- PHP ≥ 8.2 and Composer are installed
- You have a running MySQL/PostgreSQL server
- Your `.env` database credentials are correct
```
