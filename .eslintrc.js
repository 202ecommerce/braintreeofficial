/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author 202-ecommerce <tech@202-ecommerce.com>
 * @copyright Copyright (c) 202-ecommerce
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

// http://eslint.org/docs/user-guide/configuring

module.exports = {
    env: {
      browser: true,
      node: true,
      es6: true,
    },
    globals: {
      google: true,
      document: true,
      navigator: false,
      window: true,
      prestashop: true,
      $: true,
      jquery: true,
    },
    parserOptions: {
      ecmaVersion: 2017,
      sourceType: "module"
    },
    root: true,
    extends: 'airbnb-base',
    plugins: [
      'import',
      'html',
    ],
    rules: {
      'indent': ['error', 2, {'SwitchCase': 1}],
      'import/no-unresolved': 0,
      'no-use-before-define': 0,
      'function-paren-newline': ['off', 'never'],
      'object-curly-spacing': ['error', 'never'],
      'no-debugger': process.env.NODE_ENV === 'production' ? 2 : 0,
      'no-console': process.env.NODE_ENV === 'production' ? 2 : 0,
      'import/extensions': ['off', 'never'],
      'import/no-extraneous-dependencies': ['error', {'devDependencies': true}]
    }
  };
  