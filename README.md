# 勤怠管理アプリ (kintai-app)

## 📝 プロジェクト概要  
このプロジェクトは、PHPフレームワーク Laravel と Docker を用いて構築された勤怠管理アプリケーションです。  
社員の出勤・退勤・休憩の記録、月次勤怠一覧、修正申請・承認機能、管理者ダッシュボード、CSV出力など、実務に即した機能を備えています。

## 🚀 使用技術  
- Laravel (PHP)  
- Docker / docker-compose（Nginx, PHP-FPM, MySQL8, phpMyAdmin, MailHog）  
- MySQL 8  
- Laravel Fortify（認証・ユーザー管理）  
- Bladeテンプレート + CSS（Figma仕様準拠）  
- PHPUnit Feature Test（64テスト全通過）  

## 🧰 環境構築手順  
1. リポジトリをクローン  
   ```bash
   git clone https://github.com/maruenjin/kintai-app.git
   cd kintai-app

2. Dockerコンテナ構築・起動

docker compose up -d --build


3. Laravelセットアップ

cd src
composer install
cp .env.example .env
php artisan key:generate


4. データベース・マイグレーション・シーディング

php artisan migrate --seed


5. Webブラウザからの確認

http://localhost:80（Nginxポート）

MailHog：http://localhost:8025

🧮 .env 設定（主な項目）
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="no-reply@example.com"
MAIL_FROM_NAME="Kintai App"

🔑 テストユーザー / 管理者ログイン情報

Seeder（AttendanceDemoSeeder）で以下のユーザーが自動作成されます。

### 一般ユーザー（共通パスワード：password）

| 氏名       | メールアドレス        |
|------------|------------------------|
| 佐藤花子   | sato@example.com       |
| 鈴木次郎   | suzuki@example.com     |
| 田中三郎   | tanaka@example.com     |


管理者ユーザー
●管理者：山田太郎
　●Email: yamada@example.com
　●Password: password

管理者ログイン画面URL：http://localhost/admin/login


📂 ディレクトリ構成（抜粋）
kintai-app/
├ docker-compose.yml
├ docker/
│  └ …（nginx, php, mysql, mailhog 等）  
└ src/
   ├ app/
   ├ bootstrap/
   ├ config/
   ├ database/
   │  ├ migrations/
   │  └ seeds/
   ├ resources/
   │  ├ views/
   │  └ css/
   ├ routes/
   └ tests/

✅ テスト状況

Feature Test にて 64件すべて合格（GREEN）。
アプリの主要機能についてバリデーション・業務ロジック・例外処理までカバー済み。

📊 ER図

docs/kintai_er.png を参照
（提出用スプレッドシートにも貼り付け済み）