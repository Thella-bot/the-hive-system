import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
import vueParser from 'vue-eslint-parser';
import pluginPrettier from 'eslint-config-prettier';

export default [
    js.configs.recommended,
    {
        files: ['resources/js/**/*.{js,vue}'],
        plugins: {
            vue: pluginVue,
        },
        languageOptions: {
            ecmaVersion: 2022,
            sourceType: 'module',
            parser: vueParser,
            parserOptions: {
                ecmaVersion: 2022,
                sourceType: 'module',
            },
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                axios: 'readonly',
                toast: 'readonly',
                route: 'readonly',
                Ziggy: 'readonly',
                usePage: 'readonly',
                Head: 'readonly',
                Link: 'readonly',
                router: 'readonly',
                shared: 'readonly',
                fetch: 'readonly',
                URLSearchParams: 'readonly',
            },
        },
        rules: {
            ...pluginVue.configs['essential'].rules,
            'no-unused-vars': 'warn',
            'no-console': 'off',
            'vue/multi-word-component-names': 'off',
            'vue/no-v-html': 'warn',
            'vue/valid-v-bind': 'error',
            'vue/valid-v-on': 'error',
            'vue/v-bind-style': ['error', 'shorthand'],
            'vue/v-on-style': ['error', 'shorthand'],
        },
    },
    pluginPrettier,
];