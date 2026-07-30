import js from '@eslint/js';
import pluginVue from 'eslint-plugin-vue';
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
            parserOptions: {
                parser: pluginVue.parsers.vue-eslint-parser,
            },
            globals: {
                window: 'readonly',
                document: 'readonly',
                console: 'readonly',
                axios: 'readonly',
                toast: 'readonly',
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