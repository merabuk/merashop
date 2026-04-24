/** @type {import('dependency-cruiser').IConfiguration} */
module.exports = {
    forbidden: [
        {
            name: 'no-circular',
            severity: 'error',
            comment: 'Warn if there is a circular dependency',
            from: {},
            to: { circular: true }
        },

        // ─── Module Boundaries ────────────────────────────────────────────────
        // See: deptrac-frontend-modules.yaml

        {
            name: 'no-cross-module-catalog',
            comment: 'Catalog can only import from Shared, not from other business modules',
            severity: 'error',
            from: { path: '^assets/modules/Catalog/' },
            to: {
                path: '^assets/modules/',
                pathNot: '^assets/modules/(Catalog|Shared)/',
            },
        },

        {
            name: 'no-cross-module-identity-access',
            comment: 'IdentityAccess can only import from Shared, not from other business modules',
            severity: 'error',
            from: { path: '^assets/modules/IdentityAccess/' },
            to: {
                path: '^assets/modules/',
                pathNot: '^assets/modules/(IdentityAccess|Shared)/',
            },
        },

        // ─── Leaf Nodes ───────────────────────────────────────────────────────
        // See: deptrac-frontend.yaml — Types, Paths, Config, I18n have no allowed dependents

        {
            name: 'types-no-upper-layer-imports',
            comment: 'types/ cannot import from any business layers',
            severity: 'error',
            from: { path: '^assets/modules/.*/types/' },
            to: { path: '^assets/modules/.*(stores|composables|components|views|layouts|services|validators)/' },
        },

        {
            name: 'paths-no-upper-layer-imports',
            comment: 'paths/ cannot import from any business layers',
            severity: 'error',
            from: { path: '^assets/modules/.*/paths/' },
            to: { path: '^assets/modules/.*(stores|composables|components|views|layouts|services|validators)/' },
        },

        // ─── Layer Direction ──────────────────────────────────────────────────
        // See: deptrac-frontend.yaml — ruleset defines the full allowed hierarchy

        {
            name: 'validators-layer-direction',
            comment: 'validators/ can only import from types/ and external packages',
            severity: 'error',
            from: { path: '^assets/modules/.*/validators/' },
            to: {
                path: '^assets/modules/',
                pathNot: '^assets/modules/.*/types/',
            },
        },

        {
            name: 'stores-layer-direction',
            comment: 'stores/ cannot import from composables/, components/, views/, or layouts/',
            severity: 'error',
            from: { path: '^assets/modules/.*/stores/' },
            to: { path: '^assets/modules/.*(composables|components|views|layouts)/' },
        },

        {
            name: 'composables-layer-direction',
            comment: 'composables/ cannot import from views/ or layouts/',
            severity: 'error',
            from: { path: '^assets/modules/.*/composables/' },
            to: { path: '^assets/modules/.*(views|layouts)/' },
        },

        {
            name: 'components-layer-direction',
            comment: 'components/ cannot import from views/ or layouts/',
            severity: 'error',
            from: { path: '^assets/modules/.*/components/' },
            to: { path: '^assets/modules/.*(views|layouts)/' },
        },
    ],

    options: {
        doNotFollow: { path: 'node_modules' },
        tsPreCompilationDeps: true,
        tsConfig: { fileName: 'tsconfig.json' },
        enhancedResolveOptions: {
            extensions: ['.ts', '.vue', '.js'],
            conditionNames: ['import', 'require', 'node', 'default'],
        },
    },
};
