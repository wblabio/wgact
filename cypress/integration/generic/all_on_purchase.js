describe('purchase', () => {

    const wgact_options_preset = 'all-pixels-enabled.json';

    // seed options into database
    before(function (){
        // save current options to tmp file
        cy.exec('wp option get wgact_plugin_options --format=json --path=' + Cypress.env('wordpress_install_directory') + ' > ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json').its('code').should('eq', 0)

        // load preset
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + wgact_options_preset + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })

    after(function (){
        // load from before test run
        cy.exec('wp option update wgact_plugin_options < ' + Cypress.env('wgact_options_presets_folder') + 'tmp.json' + ' --format=json --path=' + Cypress.env('wordpress_install_directory')).its('code').should('eq', 0)
    })


    afterEach(() => {
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
        })
        cy.contains('Warning')
            .should('not.exist')

        cy.contains('Undefined')
            .should('not.exist')
    })
    

    it('make a purchase', () =>{

        // add simple product
        cy.visit('/product/album/')
        cy.contains('Add to cart')
            .click()

        // add variable product
        cy.visit('/product/hoodie/')
            .get('#pa_color')
                .select('Blue')
            .get('#logo')
                .select('Yes')
        cy.contains('Add to cart')
            .click()

        // add grouped product
        cy.visit('/product/logo-collection/')
            .get('.input-text.qty')
                .eq(0)
                .type('7')
            .get('.input-text.qty')
                .eq(1)
                .type('8')
            .get('.input-text.qty')
                .eq(2)
                .type('9')
        cy.contains('Add to cart')
            .click()

        cy.contains('Cart')
            .click()
        cy.contains('Proceed to checkout')
            .click()
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
        cy.wait(2000)
        cy.url().should('include','/checkout/order-received/')
        cy.wait(2000)
        cy.reload()
    })

})