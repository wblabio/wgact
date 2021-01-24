Cypress.on('uncaught:exception', (err, runnable) => {

    return false;
});

describe('update mini-cart', () => {

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
