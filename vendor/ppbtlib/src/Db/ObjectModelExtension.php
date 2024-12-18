<?php
/**
 * since 2007 PayPal
 *
 *  NOTICE OF LICENSE
 *
 *  This source file is subject to the Academic Free License (AFL 3.0)
 *  that is bundled with this package in the file LICENSE.txt.
 *  It is also available through the world-wide-web at this URL:
 *  http://opensource.org/licenses/afl-3.0.php
 *  If you did not receive a copy of the license and are unable to
 *  obtain it through the world-wide-web, please send an email
 *  to license@prestashop.com so we can send you a copy immediately.
 *
 *  DISCLAIMER
 *
 *  Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 *  versions in the future. If you wish to customize PrestaShop for your
 *  needs please refer to http://www.prestashop.com for more information.
 *
 *  @author since 2007 PayPal
 *  @author 202 ecommerce <tech@202-ecommerce.com>
 *  @copyright PayPal
 *  @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */

namespace BraintreeofficialPPBTlib\Db;

use BraintreeofficialPPBTlib\Db\ObjectModelDefinition;
use BraintreeofficialPPBTlib\Db\DbTableDefinitionModel;
use BraintreeofficialPPBTlib\Db\DbTableDefinitionRelation;
use BraintreeofficialPPBTlib\Db\DbSchema;
use BraintreeofficialPPBTlib\Db\DbTable;

class ObjectModelExtension
{
    /**
     * @var \ObjectModel
     */
    protected $om;

    /**
     * @var Db
     */
    protected $db;

    /**
     * Register ObjectModel and Db
     * @param \ObjectModel $om
     * @param Db          $db
     */
    public function __construct($om, $db)
    {
        $this->om = $om;
        $this->db = $db;
    }

    /**
     * @return bool
     */
    public function install()
    {
        $schemas = $this->getObjectModelDefinition()->getSchemas();

        return $this->createTables($schemas);
    }

    /**
     * @return bool
     */
    public function uninstall()
    {
        $names = $this->getObjectModelDefinition()->getNames();

        return $this->dropTables(array_reverse($names));
    }

    /**
     * @return BraintreeofficialPPBTlib\Db\ObjectModelDefinition (as an array collection object)
     */
    protected function getObjectModelDefinition()
    {
        return new ObjectModelDefinition($this->om->getDefinition($this->om));
    }

    /**
     * @param array $schemas
     * @return bool
     */
    protected function createTables($schemas)
    {
        return array_product(array_map(array($this, 'createTable'), $schemas));
    }

    /**
     * @param BraintreeofficialPPBTlib\Db\DbSchema $schema
     * @return bool
     */
    protected function createTable($schema)
    {
        return (new DbTable($this->db))->hydrate($schema)->create();
    }

    /**
     * @param array $names
     * @return bool
     */
    protected function dropTables(array $names)
    {
        return array_product(array_map(array($this, 'dropTable'), $names));
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function dropTable($name)
    {
        return (new DbTable($this->db))->setName($name)->drop();
    }
}
