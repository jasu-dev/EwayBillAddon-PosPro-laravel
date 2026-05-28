# EwayBill Module for POSPro

A custom Laravel module for generating and managing **E-Way Bills** inside the Acnoo POSPro script.

Built using the modular architecture of the Acnoo POSPro Laravel application, this module enables businesses to create, manage, print, and download E-Way Bills directly from the admin panel.

---

## ✨ Features

* Create E-Way Bills
* Edit Existing E-Way Bills
* Generate Printable PDF Bills
* Download E-Way Bill PDF
* Integrated with Acnoo POSPro Laravel Backend
* Clean & Maintainable Code Structure

---

## 🛠️ Built With

* PHP
* Laravel
* Blade Templates
* DOMPDF / PDF Generator

---

## 📦 Module Structure

```bash
Modules/
└── EwayBill/
    ├── App/
    ├── Database/
    ├── Config/
    ├── Resources/
    ├── Routes/
    └── ...
```

---

## 🚀 Installation

### 1. Clone or Copy Module

Copy the `EwayBill` module into your Laravel application's `Modules` directory.

```bash
Modules/EwayBill
```

---

### 2. Enable Module

```bash
php artisan module:enable EwayBill
```

---

### 3. Install Dependencies

```bash
composer install
```

---

### 4. Run Migration

```bash
php artisan migrate
```

### 5. Add Sidebar menu item

Go to sidebar layout files
```bash
App-root/
└── resources/
    └── views/layouts/business/partials/sidebar.blade.php
```
add below code under the sales menu item.

```bash
@usercan('sales.read')
  <li><a class="{{ Request::routeIs('business.eway-bills.index', 'business.eway-bills.create', 'business.eway-bills.edit') ? 'active' : '' }}" href="{{ route('business.eway-bills.index') }}">{{ __('E-Way Bills') }}</a></li>
@endusercan
```
---

### 6. Clear Cache

```bash
php artisan optimize:clear
```

---

## 📋 Usage

After installation:

1. Login to the Admin Panel
2. Navigate to the **EwayBill Module**
3. Create a New E-Way Bill
4. Edit Existing Bills
5. Print or Download PDF Bills

---

## 📄 PDF Features

The module supports:

* Printable E-Way Bill Layout
* PDF Download
* Browser Print Support
* Business & Customer Information
* Product/Invoice Details

---

## 🧩 Compatible With

* POSPro – POS Inventory Flutter App with Laravel Admin Panel
* Modular Laravel Applications

---

## 🔒 License

This project is released for personal and commercial usage.

Please ensure compliance with the original POSPro license before redistribution.

---

## 🤝 Contributing

Pull requests are welcome.

For major changes, please open an issue first to discuss what you would like to change.

---

## 👨‍💻 Author

Developed by Jasu Dev

GitHub: https://github.com/jasu-dev

---

## 📌 Notes

This is a custom add-on module developed for the POSPro Laravel application.

Original Product:

[POSPro – POS Inventory Flutter App with Laravel Admin Panel](https://codecanyon.net/item/pospro-pos-inventory-flutter-app-with-laravel-admin-panel/53621221?utm_source=chatgpt.com)

---

## ⭐ Support

If you like this project, please consider giving it a ⭐ on GitHub.
