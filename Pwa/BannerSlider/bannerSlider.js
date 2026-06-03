import React, { useState, useEffect, useRef, useMemo } from 'react';
import { useQuery } from '@apollo/client';
import { GET_BANNER_SLIDER, GET_URLS } from './bannerSlider.gql';
import { useStyle } from '@magento/venia-ui/lib/classify';
import { Link } from 'react-router-dom';
import defaultClasses from './bannerSlider.module.css';

const decodeHtml = html => {
    if (typeof document === 'undefined') {
        return html;
    }

    const txt = document.createElement('textarea');
    txt.innerHTML = html;

    return txt.value;
};

const BannerSlider = props => {
    const classes = useStyle(defaultClasses, props.classes);

    const [current, setCurrent] = useState(0);

    const autoplayRef = useRef(null);

    const { data, loading, error } = useQuery(GET_BANNER_SLIDER, {
        fetchPolicy: 'cache-and-network'
    });

    const items = data?.BannerSliderInfo?.items || [];

    const activeItems = useMemo(
        () =>
            items.filter(
                item => item.status === '1' || item.status === 1
            ),
        [items]
    );

    // Product SKUs
    const productSkus = useMemo(
        () => [
            ...new Set(
                activeItems
                    .filter(
                        item =>
                            item.link_type === 'link_type_product' &&
                            item.sku
                    )
                    .map(item => String(item.sku))
            )
        ],
        [activeItems]
    );

    // Category IDs
    const categoryIds = useMemo(
        () => [
            ...new Set(
                activeItems
                    .filter(
                        item =>
                            item.link_type === 'link_type_category' &&
                            item.link_type_resource
                    )
                    .map(item => String(item.link_type_resource))
            )
        ],
        [activeItems]
    );

    /**
     * Single GraphQL Request
     * Fetch products and categories together
     */
    const { data: urlsData } = useQuery(GET_URLS, {
        variables: {
            sku: productSkus,
            ids: categoryIds
        },
        skip:
            productSkus.length === 0 &&
            categoryIds.length === 0,
        fetchPolicy: 'cache-and-network'
    });

    // Product URL Map
    const productUrlMap = useMemo(() => {
        const map = {};

        urlsData?.products?.items?.forEach(product => {
            const suffix = product.url_suffix || '';

            map[String(product.sku)] =
                `/${product.url_path || product.url_key}${suffix}`;
        });

        return map;
    }, [urlsData]);

    // Category URL Map
    const categoryUrlMap = useMemo(() => {
        const map = {};

        urlsData?.categories?.items?.forEach(category => {
            const suffix = category.url_suffix || '';

            map[String(category.id)] =
                `/${category.url_path || category.url_key}${suffix}`;
        });

        return map;
    }, [urlsData]);

    const stopAutoplay = () => {
        if (autoplayRef.current) {
            clearInterval(autoplayRef.current);
        }
    };

    const startAutoplay = () => {
        stopAutoplay();

        autoplayRef.current = setInterval(() => {
            setCurrent(prev =>
                prev === activeItems.length - 1 ? 0 : prev + 1
            );
        }, 5000);
    };

    useEffect(() => {
        if (activeItems.length > 1) {
            startAutoplay();
        }

        return () => stopAutoplay();
    }, [activeItems.length]);

    if (loading && !data) {
        return (
            <div className={classes.loadingContainer}>
                <div className={classes.spinner} />
            </div>
        );
    }

    if (error || activeItems.length === 0) {
        return null;
    }

    const nextSlide = () => {
        setCurrent(
            current === activeItems.length - 1
                ? 0
                : current + 1
        );

        startAutoplay();
    };

    const prevSlide = () => {
        setCurrent(
            current === 0
                ? activeItems.length - 1
                : current - 1
        );

        startAutoplay();
    };

    const setSlide = index => {
        setCurrent(index);
        startAutoplay();
    };

    return (
        <div
            className={classes.root}
            onMouseEnter={stopAutoplay}
            onMouseLeave={startAutoplay}
        >
            <div className={classes.sliderContainer}>
                {activeItems.map((banner, index) => {
                    const isActive = index === current;

                    const slideClass = `${classes.slide} ${
                        isActive ? classes.slideActive : ''
                    }`;

                    const productUrl =
                        productUrlMap[String(banner.sku)];

                    const categoryUrl =
                        categoryUrlMap[
                            String(banner.link_type_resource)
                        ];

                    return (
                        <div
                            key={banner.entity_id}
                            className={slideClass}
                        >
                            {(banner.resource_type ===
                                'local_image' ||
                                banner.resource_type ===
                                    'external_image') && (
                                <img
                                    className={classes.image}
                                    src={banner.resource_path}
                                    alt={
                                        banner.alt_text ||
                                        banner.title
                                    }
                                />
                            )}

                            {banner.resource_type ===
                                'youtube_video' && (
                                <div
                                    className={
                                        classes.videoContainer
                                    }
                                >
                                    <iframe
                                        className={classes.video}
                                        src={banner.resource_path}
                                        title={banner.title}
                                        allowFullScreen
                                    />
                                </div>
                            )}

                            {banner.resource_type ===
                                'custom_html' && (
                                <div
                                    className={
                                        classes.htmlContainer
                                    }
                                    dangerouslySetInnerHTML={{
                                        __html: decodeHtml(
                                            banner.resource_path
                                        )
                                    }}
                                />
                            )}

                            <div className={classes.overlay}>
                                <h2 className={classes.title}>
                                    {banner.title}
                                </h2>

                                {banner.alt_text && (
                                    <p
                                        className={
                                            classes.description
                                        }
                                    >
                                        {banner.alt_text}
                                    </p>
                                )}

                                {banner.link_type ===
                                    'link_type_product' &&
                                    productUrl && (
                                        <Link
                                            to={productUrl}
                                            className={
                                                classes.button
                                            }
                                        >
                                            Shop Now
                                        </Link>
                                    )}

                                {banner.link_type ===
                                    'link_type_category' &&
                                    categoryUrl && (
                                        <Link
                                            to={categoryUrl}
                                            className={
                                                classes.button
                                            }
                                        >
                                            Shop Now
                                        </Link>
                                    )}

                                {banner.link_type ===
                                    'link_type_custom' &&
                                    banner.link_type_resource && (
                                        <a
                                            href={
                                                banner.link_type_resource
                                            }
                                            className={
                                                classes.button
                                            }
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Shop Now
                                        </a>
                                    )}
                            </div>
                        </div>
                    );
                })}
            </div>

            {activeItems.length > 1 && (
                <>
                    <button
                        className={`${classes.navButton} ${classes.navButtonLeft}`}
                        onClick={prevSlide}
                        aria-label="Previous Slide"
                    >
                        &#10094;
                    </button>

                    <button
                        className={`${classes.navButton} ${classes.navButtonRight}`}
                        onClick={nextSlide}
                        aria-label="Next Slide"
                    >
                        &#10095;
                    </button>

                    <div className={classes.dotsContainer}>
                        {activeItems.map((_, index) => (
                            <button
                                key={index}
                                className={`${classes.dot} ${
                                    index === current
                                        ? classes.dotActive
                                        : ''
                                }`}
                                onClick={() =>
                                    setSlide(index)
                                }
                                aria-label={`Go to slide ${
                                    index + 1
                                }`}
                            />
                        ))}
                    </div>
                </>
            )}
        </div>
    );
};

export default BannerSlider;
