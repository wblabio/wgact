describe('Google Analytics eec events', () => {

    const wgact_options_preset = 'all-pixels-enabled.json';
    // const wgact_options_preset_conversion_cart_data_off     = 'all-pixels-enabled_conversion-cart-data-off.json';
    // const wgact_options_preset_cookie_consent_fully_enabled = 'all-pixels-enabled_cookie-consent-fully-enabled.json';


    // seed options into database
    before(function () {
        // save current options to tmp file
        cy.exec('wp option get wgact_plugin_options --format=json --path=' + Cypress.env('wordpress_install_directory') + ' > ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json').its('code').should('eq', 0)

        // load preset
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    after(function () {
        // load from before test run
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json' + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    afterEach(() => {
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
            expect($html).to.not.contain('Undefined index')
        })
        cy.contains('Warning')
            .should('not.exist')
    })

    it('add to cart on /shop/ page', () => {

        // visit /shop/ page
        cy.visit('/shop/')

        // add to an item to the cart
        cy.get('.add_to_cart_button')
            .eq(0)
            .click()

        // cy.contains('Add to cart')
        //     .click()
        // cy.contains('Add to cart')
        //     .eq(1)
        //     .click()

        //remove the product from the cart
        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .wait(200)

        cy.get('.woocommerce-mini-cart-item')
            .get('.remove_from_cart_button')
            .click({force:true})
            .wait(400)
    })

    it('ado to cart WC product page: simple product', () => {

        // visit /product/album/ page
        cy.visit('/product/album/')

        // add to an item to the cart
        cy.get('.single_add_to_cart_button')
            .eq(0)
            .click()

        //remove the product from the cart
        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .wait(200)

        cy.get('.woocommerce-mini-cart-item')
            .get('.remove_from_cart_button')
            .click({force:true})
            .wait(400)
    })

    it('ado to cart WC product page: variable product', () => {

        // visit /product/hoodie/ page
        cy.visit('/product/hoodie/')
            .get('#pa_color')
            .select('Blue')
            .get('#logo')
            .select('Yes')
        cy.contains('Add to cart')
            .click()

        //remove the product from the cart
        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .wait(200)

        cy.get('.woocommerce-mini-cart-item')
            .get('.remove_from_cart_button')
            .click({force:true})
            .wait(400)
    })

    it('ado to cart WC product page: grouped product', () => {

        // add grouped product
        cy.visit('/product/logo-collection/')
            .get('.input-text.qty')
                .eq(0)
                .type('3')
            .get('.input-text.qty')
                .eq(1)
                .type('4')
            .get('.input-text.qty')
                .eq(2)
                .type('5')

        cy.contains('Add to cart')
            .click()

        //remove the product from the cart
        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .wait(200)

        cy.get('.woocommerce-mini-cart-item')
            .get('.remove_from_cart_button')
                .eq(0)
                .click({force:true})
                .wait(400)
            .get('.remove_from_cart_button')
                .eq(0)
                .click({force:true})
                .wait(400)
            .get('.remove_from_cart_button')
                .eq(0)
                .click({force:true})
                .wait(400)
    })

    // it('ado to cart WC product page: external product', () => {
    //
    //     // add grouped product
    //     cy.visit('/product/wordpress-pennant/')
    //
    //     cy.get('.single_add_to_cart_button')
    //         .click()
    //
    // })

    // test a subscription
})
