describe('purchase', () => {

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

        cy.visit('/product/album/')
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
    })

})