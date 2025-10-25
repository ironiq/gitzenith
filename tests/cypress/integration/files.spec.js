describe('File page', () => {
  it('view file contents', () => {
    cy.visit('/git-bare-repo/blob/HEAD/mm/cma.c');

    cy.get('.breadcrumb > .active').should('have.text', '\n            cma.c\n          ');
    cy.get('.ace_comment:nth(1)').should('have.text', ' * Contiguous Memory Allocator');
  });

  it('view file raw', () => {
    cy.request('/git-bare-repo/raw/HEAD/mm/cma.c').then((resp) => {
      expect(resp.status).to.eq(200);
      expect(resp.body).to.include(' * Contiguous Memory Allocator');
    });
  });
});
