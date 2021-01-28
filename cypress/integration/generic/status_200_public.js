describe('status 200 public', () => {

    afterEach(() =>{
        cy.get('html').should(($html) => {
            expect($html).to.not.contain('Fatal error')
            expect($html).to.not.contain('Undefined index')
        })
        cy.contains('Warning')
            .should('not.exist')
    })

    it('visit WC front page', () =>{
        cy.visit('/')
    })

    it('visit WC shop page', () =>{
        cy.visit('/shop/')
    })

    it('visit WC product page', () =>{
        cy.visit('/product/album/')
    })

    it('visit WC product category page', () =>{
        cy.visit('/product-category/music/')
    })

    it('visit WC product tag page', () =>{
        cy.visit('/product-tag/funny/')
    })

    it('visit WC cart page', () =>{
        cy.visit('/cart/')
    })

    it('visit WC search attribute page', () =>{
        cy.visit('/shop/?color=Blue')
    })

    it('visit WC purchase confirmation page', () =>{
        cy.visit('/checkout/order-received/61/?key=wc_order_nPUFu8qoiCSkv')
    })

    it('visit WC regular page', () =>{
        cy.visit('/sample-page/')
    })

    // it('visit WC post page', () =>{
    //     cy.visit('/2019/12/13/hello-world/')
    // })

    it('visit WC 404 page', () =>{
        cy.visit('/abcd/', {failOnStatusCode: false})
    })

    it('visit login page', () =>{
        cy.visit('/wp-admin/')
    })
})
