describe('Google gtag events', () => {

    // Cypress.on('window:before:load', (win) => {
    //     // because this is called before any scripts
    //     // have loaded - the ga function is undefined
    //     // so we need to create it.
    //     win.gtag = cy.spy().as('gtag')
    // })

    // https://www.cypress.io/blog/2018/02/05/when-can-the-test-start/
    // let appHasStarted
    // function spyOnAddEventListener (win) {
    //     // win = window object in our application
    //     const addListener = win.EventTarget.prototype.addEventListener
    //     win.EventTarget.prototype.addEventListener = function (name) {
    //         if (name === 'wooptpmAddToCart') {
    //             // web app added an event listener to the input box -
    //             // that means the web application has started
    //             appHasStarted = true
    //             // restore the original event listener
    //             win.EventTarget.prototype.addEventListener = addListener
    //         }
    //         return addListener.apply(this, arguments)
    //     }
    // }
    //
    // function waitForAppStart() {
    //     // keeps rechecking "appHasStarted" variable
    //     return new Cypress.Promise((resolve, reject) => {
    //         const isReady = () => {
    //             if (appHasStarted) {
    //                 return resolve()
    //             }
    //             setTimeout(isReady, 0)
    //         }
    //         isReady()
    //     })
    // }

    const wgact_options_preset = Cypress.env('wgact_options_preset');

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

    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__google-analytics/cypress/integration/ga-method-stubbing.js
    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__window-fetch/cypress/integration/spy-on-fetch-spec.js

    it('fire gtag add_to_cart on /shop/ page', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()

            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                cy.get('@gtag').should('be.calledOnce')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            }

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')

            // cy.get('@gtag').should(($gtag) => {
            //     expect($gtag).to.have.been.calledWith('event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
            // })

            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /product-category/music/ page', () => {

        cy.visit('/product-category/music/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.called')
            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')


            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                cy.get('@gtag').should('be.calledOnce')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            }
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /product-tag/funny/ page', () => {

        cy.visit('/product-tag/funny/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.called')
            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')

            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                cy.get('@gtag').should('be.calledOnce')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            }
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /?s=beanie&post_type=product page', () => {

        cy.visit('/?s=beanie&post_type=product')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.called')
            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')
            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                cy.get('@gtag').should('be.calledOnce')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            }
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /product/album/ page', () => {

        cy.visit('/product/album/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.single_add_to_cart_button')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.called')
            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledThrice')
            if (Cypress.env('plugin_version') === 'premium') {
                // cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
                // cy.get('@gtag').should('be.calledWith', 'event', 'view_item', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                // cy.get('@gtag').should('be.calledTwice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
                // cy.get('@gtag').should('be.calledWith', 'event', 'view_item', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            }
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /product/hoodie/ page', () => {

        cy.visit('/product/hoodie/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            cy.get('#pa_color')
                .select('Blue')
                .get('#logo')
                .select('Yes')

            cy.contains('Add to cart')
                .click()

            // cy.get('@gtag').should('be.called')
            cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    it('fire gtag add_to_cart on /product/logo-collection/ page', () => {

        cy.visit('/product/logo-collection/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag')

            // add grouped product
            cy.get('.input-text.qty')
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

            // cy.get('@gtag').should('be.called')
            cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')

            // cy.wait(400)
            // cy.clearCookies()

        })
    })

    it('fire gtag remove_from_cart on /shop/ page', () => {

        cy.visit('/shop/')

        // wait for events to register
        cy.wait(400)

        // cy.visit('/shop/',{
        //     onBeforeLoad: spyOnAddEventListener
        // }).then(waitForAppStart)

        cy.window().then((win) => {
            cy.spy(win, 'gtag').as('gtag')
        })

        // add to an item to the cart
        // cy.get('.add_to_cart_button')
        //     .eq(0)
        //     .click()
        //     .then(() => {
        //
        //         cy.wait(500)
        //         cy.get('[id="site-header-cart"]')
        //             .trigger('mouseover')
        //             .should('be.visible')
        //             .get('.remove_from_cart_button')
        //             .click({
        //                 force   : true,
        //                 multiple: true
        //             })
        //     })

        cy.get('.add_to_cart_button')
            .eq(0)
            .click()

        cy.wait(400)

        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .should('be.visible')
            .get('.remove_from_cart_button')
            .click({
                force   : true,
                multiple: true
            })


        // remove from cart
        // cy.get('[id="site-header-cart"]')
        //     .trigger('mouseover')
        //     .should('be.visible')
        //     .get('.remove_from_cart_button')
        //     .click({
        //         force   : true,
        //         multiple: true
        //     })

        if (Cypress.env('plugin_version') === 'premium') {
            cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        } else {
            // no event to be checked
        }

    })

    it('fire gtag remove_from_cart on /cart/ page with remove button', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(400)

        // add to an item to the cart
        cy.get('.add_to_cart_button')
            .eq(0)
            .click()
        cy.get('.add_to_cart_button')
            .eq(1)
            .click()
        cy.get('.add_to_cart_button')
            .eq(1)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()

        cy.wait(400)

        cy.visit('/cart/')

        cy.wait(400)


        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // remove from cart
            cy.get('.remove')
                .eq(0)
                .click()

            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
            } else {
                // no event to be checked
            }
        })
    })

    it('fire gtag add_to_cart and remove_from_cart on /cart/ page with update button', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(800)

        // add to an item to the cart
        cy.get('.add_to_cart_button')
            .eq(0)
            .click()
        cy.get('.add_to_cart_button')
            .eq(1)
            .click()
        cy.get('.add_to_cart_button')
            .eq(1)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()
        cy.get('.add_to_cart_button')
            .eq(2)
            .click()

        // https://glebbahmutov.com/cypress-examples/7.0.1/recipes/find-object.html
        cy.window()
            .its('wooptpmDataLayer.cart')
            .should('not.be.empty')
            .then((cart) => {
                let names = [];
                for (const [key, value] of Object.entries(cart)) {
                    names.push({name: value.name});
                }
                return names;
            })
            .should('deep.include', {
                name: 'Album',
            })
            .should('deep.include', {
                name: 'Beanie',
            })
            .should('deep.include', {
                name: 'Beanie with Logo',
            })

        // wait for WC Ajax to add all products internally
        cy.wait(800)

        cy.visit('/cart/')

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            cy.get('.input-text.qty')
                .eq(0)
                .type('{backspace}2')

            cy.get('.input-text.qty')
                .eq(2)
                .type('{backspace}2')

            // update cart
            cy.get("[name='update_cart']")
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')

            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
                cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
            } else {
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
            }
        })
    })

    it('fire gtag select_content on /shop/ page', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            //https://github.com/cypress-io/cypress/issues/1203
            // cy.get('.product').then(form$ => {
            //     form$.on('click', e => {
            //         e.preventDefault()
            //     })
            // })

            // add to an item to the cart
            cy.get('.product')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'select_content')
            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledTwice')
                cy.get('@gtag').should('be.calledWith', 'event', 'select_item', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                // cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                // no gtag event to be logged in the free version
            }
        })
    })

    it('fire gtag select_content on /product-category/music/ page', () => {

        cy.visit('/product-category/music/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.product')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'select_content')
            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledTwice')
                cy.get('@gtag').should('be.calledWith', 'event', 'select_item', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                // cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                // no gtag event to be logged in the free version

            }
        })
    })

    it('fire gtag select_content on /product-tag/funny/ page', () => {

        cy.visit('/product-tag/funny/')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.product')
                .eq(0)
                .click()

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'select_content')
            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledTwice')
                cy.get('@gtag').should('be.calledWith', 'event', 'select_item', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                // cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                // no gtag event to be logged in the free version

            }
        })
    })

    it('fire gtag select_content on /?s=beanie&post_type=product page', () => {

        cy.visit('/?s=beanie&post_type=product')

        // wait for events to get registered
        cy.wait(400)

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.product')
                .eq(0)
                .click()

            cy.window().its('wooptpmDataLayer').should('have.property', 'cart')

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'select_content')
            if (Cypress.env('plugin_version') === 'premium') {
                cy.get('@gtag').should('be.calledTwice')
                cy.get('@gtag').should('be.calledWith', 'event', 'select_item', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
                // cy.get('@gtag').should('be.calledWith', 'event', 'select_content', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
            } else {
                // no gtag event to be logged in the free version

            }
        })
    })

    it('fire gtag begin_checkout on /cart/ page', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(800)

        // add to an item to the cart
        cy.get('.add_to_cart_button')
            .eq(0)
            .click()

        cy.window()
            .its('wooptpmDataLayer.cart')
            .should('not.be.empty')

        // WC has a slow Ajax call to update the cart
        // We should replace cy.wait() with a cy.intercept() and wait until the call is finished
        // https://docs.cypress.io/guides/guides/network-requests#Waiting
        cy.wait(800)

        cy.visit('/cart/')

        cy.window().then((win) => {
            cy.spy(win, 'gtag').as('gtag');
        })

        cy.get('.checkout-button')
            .eq(0)
            .click()

        cy.window().its('wooptpmDataLayer').should('have.property', 'cart')

        // cy.get('@gtag').should('be.calledWith', 'event', 'begin_checkout')
        // cy.get('@gtag').should('be.calledTwice')
        // cy.get('@gtag').should('be.calledWith', 'event', 'begin_checkout', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
        // cy.get('@gtag').should('be.calledWith', 'event', 'begin_checkout', Cypress.sinon.match.has("send_to", "UA-39746956-9"))

        if (Cypress.env('plugin_version') === 'premium') {
            cy.get('@gtag').should('be.calledWith', 'event', 'begin_checkout', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
            cy.get('@gtag').should('be.calledWith', 'event', 'begin_checkout', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
        } else {
            // no gtag event to be logged in the free version
        }
    })

    it('fire gtag add_to_cart and remove_from_cart on /cart/ page with update button', () => {

        cy.visit('/shop/')

        // wait for events to get registered
        cy.wait(400)

        // add to an item to the cart
        cy.get('.add_to_cart_button')
            .eq(0)
            .click()

        cy.window()
            .its('wooptpmDataLayer.cart')
            .should('not.be.empty')

        // wait WC Ajax call to update the internal cart
        cy.wait(1000)

        cy.visit('/checkout/')

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            cy.get('[id="billing_first_name"]')
                .type('John')
            cy.get('[id="billing_last_name"]')
                .type('Doe')
            cy.get('[id="select2-billing_country-container"]')
                .type('Germany{enter}')
            cy.get('[id="billing_address_1"]')
                .type('Example Street 1')
            cy.get('[id="billing_postcode"]')
                .type('12345')
            cy.get('[id="billing_city"]')
                .type('Example City')
            cy.get('[id="billing_phone"]')
                .type('987654321')
            cy.get('[id="billing_email"]')
                .type('test@example.com')
            cy.contains('Place order')
                .click()

            // cy.get('@gtag').should('be.calledWith', 'event', 'set_checkout_option')
            // cy.get('@gtag').should('be.calledTwice')


            if (Cypress.env('plugin_version') === 'premium') {
                // cy.get('@gtag').should('be.calledWith', 'event', 'set_checkout_option', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
                cy.get('@gtag').should('be.calledWith', 'event', 'set_checkout_option', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
            } else {
                // no gtag event to be logged in the free version
            }
        })
    })

    // tests to write
    // it('add_to_cart on related products on product page', () => {})
    // it('no add_to_cart on clicks on select options button', () => {})
    // it('', () => {})
    // it('', () => {})
    // it('', () => {})
    // it('', () => {})
    // it('', () => {})
    // it('', () => {})
})
