# MageMasani Responsive Banner Slider for Magento 2

[![Magento 2.4.x](https://img.shields.io/badge/Magento-2.4.x-orange.svg)](https://magento.com/)
[![PHP 8.1 - 8.5](https://img.shields.io/badge/PHP-8.1%20--%208.5-blue.svg)](https://www.php.net/)
[![License: GPL-3.0-or-later](https://img.shields.io/badge/License-GPL--3.0--or--later-green.svg)](https://opensource.org/licenses/GPL-3.0)

A high-performance, fully responsive **Banner Slider Module** for Magento 2 with support for multiple resource types (images, videos, custom HTML), widget embedding, customer group restrictions, and complete GraphQL API compatibility.

---

## 🚀 Features

- **Multi-Resource Support**:
  - Local Image Uploads (with media manager integration)
  - External Image URLs
  - YouTube Video Embeds
  - Custom Responsive HTML content with widget directive processing
- **Widget System**: Place sliders anywhere via Magento PageBuilder or CMS Widgets.
- **Customer Group Permissions**: Target specific customer groups per banner.
- **Scheduling**: Define active start date and end date per banner.
- **GraphQL Integration**: Seamless headless support via `MageMasani_BannerSliderGraphQl`.
- **PHP 8.1 – 8.5 & Magento Coding Standard 100% Compliant**:
  - Zero direct `ObjectManager` calls.
  - Strict typing (`declare(strict_types=1);`) across all classes.
  - Declarative schema with synchronized `db_schema_whitelist.json`.

---

## 📦 Installation

```bash
composer require magemasani/module-bannerslider
bin/magento module:enable MageMasani_BannerSlider
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f
bin/magento cache:flush
```

---

## 🛠️ Architecture

- **Tables**:
  - `magemasani_bannerslider_slider`: Stores slider configurations and layouts.
  - `magemasani_bannerslider_banner`: Stores banners, media links, and scheduling.
  - `magemasani_bannerslider_customer_group`: Handles customer group association.
- **Admin UI**: Built with UI Components (`bannerslider_slider_listing`, `bannerslider_banner_listing`, form modifiers).

---

## 📄 License

GPL-3.0-or-later © [MageMasani](https://www.magemasani.com/)
