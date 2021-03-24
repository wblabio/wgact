Cypress.on('uncaught:exception', (err, runnable) => {

    return false;
});

describe('update mini-cart', () => {

    const wgact_options_preset = Cypress.env('wgact_options_preset');

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

    // afterEach(() =>{
    //     cy.get('html').should(($html) => {
    //         expect($html).to.not.contain('Fatal error')
    //     })
    //     cy.contains('Warning')
    //         .should('not.exist')
    // })

    it('add and remove a product to and from mini-cart', () =>{
        cy.visit('/shop/')
            .wait(200)
        cy.contains('Add to cart')
            .click()
            .wait(300)
        cy.contains('1 item')
        cy.get('[id="site-header-cart"]')
            .trigger('mouseover')
            .wait(200)
        cy.get('.woocommerce-mini-cart-item')
            .get('.remove_from_cart_button')
            .click({force:true})
            .wait(400)
        cy.visit('/shop/')
        cy.get('.site-header-cart')
            .contains('0 items')
    })
})
