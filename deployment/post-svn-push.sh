#!/bin/bash

wp plugin deactivate woocommerce-google-adwords-conversion-tracking-tag --path='/Users/aleksandarvucenovic/dev/apps/wordpress-deployment/'
rm ~/dev/apps/wordpress-deployment/wp-content/plugins/woocommerce-google-adwords-conversion-tracking-tag
wp plugin install woocommerce-google-adwords-conversion-tracking-tag --path='/Users/aleksandarvucenovic/dev/apps/wordpress-deployment/'
wp plugin activate woocommerce-google-adwords-conversion-tracking-tag --path='/Users/aleksandarvucenovic/dev/apps/wordpress-deployment/'