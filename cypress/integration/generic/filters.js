describe('Google gtag events', () => {

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

    // Cypress.on('window:before:load', (win) => {
    //     // because this is called before any scripts
    //     // have loaded - the ga function is undefined
    //     // so we need to create it.
    //     // win.gtag = cy.stub()
    //     cy.spy(win, 'gtag').as('gtag');
    // })

    // beforeEach(function () {
    //     // cy.intercept('www.google-analytics.com', { statusCode: 503 })
    //     cy.visit('/')
    // })

    // beforeEach(function () {
    //     // application prints "hello" to the console
    //     // during the page load, thus we need to create
    //     // our spy as soon as the window object is created but
    //     // before the page loads
    //     cy.visit('/shop/', {
    //         onBeforeLoad (win) {
    //             cy.spy(win, 'gtag').as('gtag')
    //         },
    //     })
    // })

    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__google-analytics/cypress/integration/ga-method-stubbing.js
    // https://github.com/cypress-io/cypress-example-recipes/blob/master/examples/stubbing-spying__window-fetch/cypress/integration/spy-on-fetch-spec.js
    it('fire gtag add_to_cart on /shop/ page', () => {

        cy.visit('/shop/?dynr')

        cy.window().then((win) => {

            cy.spy(win, 'gtag').as('gtag');

            // add to an item to the cart
            cy.get('.add_to_cart_button')
                .eq(0)
                .click()


            if (Cypress.env('plugin_version') === 'premium') {
                // cy.get('@gtag').should('be.calledThrice')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
                // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("items", Cypress.sinon.match.has("id", "custm_googXX_22")))

            } else {
                // cy.get('@gtag').should('be.calledOnce')
                cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart')
                // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("items", Cypress.sinon.match.has("id", "custm_googXX_23")))
            }
            // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
            // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
            // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))

            // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("items", {id: "custm_googXX_22", quantity: 1, price: 15, google_business_vertical: "retail"}))

            // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')

            // cy.get('@gtag').should(($gtag) => {
            //     expect($gtag).to.have.been.calledWith('event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
            // })

            // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
            // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
        })
    })

    // it('test conversion prevention filter on purchase confirmation page', () => {
    //
    //     // cy.visit('/shop/', {
    //     //     onBeforeLoad(win) {
    //     //         win.gtag = cy.stub()
    //     //     },
    //     // })
    //
    //     // cy.visit('/shop/')
    //     // cy.window().its('gtag').window()
    //     // cy.window().then((win) => {
    //     //
    //     //     cy.spy(win, 'gtag').as('gtag');
    //     // })
    //
    //     // cy.get('@gtag')
    //
    //     // const gtag = cy.stub().as('gtag')
    //     // cy.on('window:before:load', (win) => {
    //     //     Object.defineProperty(win, 'gtag', {
    //     //         configurable: false,
    //     //         get: () => gtag, // always return the stub
    //     //         set: () => {}, // don't allow actual google analytics to overwrite this property
    //     //     })
    //     // })
    //     //
    //     // cy.visit('/shop/')
    //
    //     // let gaStub
    //     //
    //     // gaStub = cy.spy()
    //     // cy.visit(Cypress.env('purchase_confirmation_url') + '&nodedupe', {
    //     //     onBeforeLoad: (contentWindow) => {
    //     //         Object.defineProperties(contentWindow, {
    //     //             'gtag': {
    //     //                 value: gaStub,
    //     //                 writable: false
    //     //             }
    //     //         })
    //     //     }
    //     // })
    //
    //     // cy.visit(Cypress.env('purchase_confirmation_url') + '&nodedupe')
    //
    //     // cy.visit(Cypress.env('purchase_confirmation_url'), {
    //     //     onBeforeLoad (win) {
    //     //         // start spying
    //     //         cy.spy(win, 'gtag').as('gtag');
    //     //     }
    //     // })
    //
    //     // cy.window().then((win) => {
    //     //
    //     //     cy.spy(win, 'gtag').as('gtag');
    //     //
    //     //     // add to an item to the cart
    //     //     // cy.get('.add_to_cart_button')
    //     //     //     .eq(0)
    //     //     //     .click()
    //     //
    //     //     // cy.get('@gtag').should('be.calledThrice')
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "G-YQBXCRGVLT"))
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("send_to", ["AW-965183221"]))
    //     //
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("items", {id: "custm_googXX_22", quantity: 1, price: 15, google_business_vertical: "retail"}))
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'add_to_cart', Cypress.sinon.match.has("items", Cypress.sinon.match.has("id", "custm_googXX_22")))
    //     //
    //     //     // cy.get('@gtag').should('be.calledOnceWith', 'event', 'add_to_cart')
    //     //
    //     //     // cy.get('@gtag').should(($gtag) => {
    //     //     //     expect($gtag).to.have.been.calledWith('event', 'add_to_cart', Cypress.sinon.match.has("send_to", "UA-39746956-9"))
    //     //     // })
    //     //
    //     //     // cy.get('@gtag').should('be.calledTwiceWith', 'event', 'add_to_cart')
    //     //     // cy.get('@gtag').should('be.calledWith', 'event', 'remove_from_cart')
    //     // })
    //
    //     // cy.visit(Cypress.env('purchase_confirmation_url') + '&conversion_prevention_filter')
    //     // cy.visit(Cypress.env('purchase_confirmation_url'))
    //
    //
    // })
})
