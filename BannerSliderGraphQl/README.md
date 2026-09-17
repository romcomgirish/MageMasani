# MageMasani Banner Slider GraphQL for Magento 2

[![Magento 2.4.x](https://img.shields.io/badge/Magento-2.4.x-orange.svg)](https://magento.com/)
[![PHP 8.1 - 8.5](https://img.shields.io/badge/PHP-8.1%20--%208.5-blue.svg)](https://www.php.net/)
[![License: GPL-3.0-or-later](https://img.shields.io/badge/License-GPL--3.0--or--later-green.svg)](https://opensource.org/licenses/GPL-3.0)

GraphQL extension for **`MageMasani_BannerSlider`**, providing headless endpoints with caching identities, filtering, sorting, pagination, and product SKU resolution.

---

## 🚀 GraphQL Query Example

```graphql
query GetBanners {
  BannerSliderInfo(
    filter: {
      slider_id: { eq: "1" }
      resource_type: { eq: "local_image" }
    }
    pageSize: 10
    currentPage: 1
    sort: {
      sort_order: ASC
    }
  ) {
    total_count
    items {
      entity_id
      slider_id
      title
      resource_type
      resource_path
      alt_text
      link_type
      link_type_resource
      sku
      status
      sort_order
      start_date
      end_date
    }
  }
}
```

---

## 📦 Installation

```bash
composer require magemasani/module-bannerslidergraphql
bin/magento module:enable MageMasani_BannerSlider MageMasani_BannerSliderGraphQl
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento cache:flush
```

---

## 📄 License

GPL-3.0-or-later © [MageMasani](https://www.magemasani.com/)
