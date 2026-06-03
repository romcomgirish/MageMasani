import { gql } from '@apollo/client';

export const GET_BANNER_SLIDER = gql`
    query GetBannerSlider {
        BannerSliderInfo(currentPage: 1, filter: {}, pageSize: 10) {
            items {
                alt_text
                created_at
                end_date
                entity_id
                link_type
                link_type_resource
                resource_path
                sku
                resource_type
                slider_id
                sort_order
                start_date
                status
                title
                updated_at
            }
            total_count
        }
    }
`;

export const GET_URLS = gql`
    query GetUrls($sku: [String], $ids: [String!]) {
        products(filter: { sku: { in: $sku } }) {
            items {
                sku
                uid
                url_path
                url_key
                url_suffix
            }
        }

        categories(filters: { ids: { in: $ids } }) {
            items {
                uid
                id
                url_path
                url_key
                url_suffix
            }
        }
    }
`;
