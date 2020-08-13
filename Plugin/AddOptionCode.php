<?php
namespace SnowIO\AttributeOptionCode\Plugin;

use Magento\Catalog\Setup\CategorySetup;
use Magento\Framework\Registry;
use SnowIO\AttributeOptionCode\Api\AttributeOptionCodeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use SnowIO\AttributeOptionCode\Block\Adminhtml\Attribute\Edit\Options\Visual;
use SnowIO\AttributeOptionCode\Block\Adminhtml\Attribute\Edit\Options\Text;
use SnowIO\AttributeOptionCode\Block\Adminhtml\Attribute\Edit\Options\Options;

class AddOptionCode
{
    /** @var Registry */
    protected $registry;

    /** @var AttributeOptionCodeRepositoryInterface */
    private $attributeOptionCodeRepository;

    /**
     * AddOptionCode constructor.
     * @param AttributeOptionCodeRepositoryInterface $attributeOptionCodeRepository
     * @param Registry $registry
     */
    public function __construct(
        AttributeOptionCodeRepositoryInterface $attributeOptionCodeRepository,
        Registry $registry
    ) {
        $this->attributeOptionCodeRepository = $attributeOptionCodeRepository;
        $this->registry = $registry;
    }

    /**
     * @param Visual|Text|Option $subject
     * @param array $result
     * @return array
     */
    public function afterGetOptionValues($subject, array $result)
    {
        $this->addOptionCodeToOptionValuesData($result);
        return $result;
    }

    /**
     * @param $values
     * @return array
     */
    private function addOptionCodeToOptionValuesData(array $values): array
    {
        $ids = $this->getIdsFromValuesData($values);
        $attributeCode = $this->getAttributeObject()->getAttributeCode();
        $optionCodes = $this->getOptionCodes($attributeCode, $ids);

        return $this->mapOptionCodeWithValuesData($optionCodes, $values);
    }

    /**
     * @param string $attributeCode
     * @param array $ids
     * @return array
     */
    private function getOptionCodes(string $attributeCode, array $ids): array
    {
        return $this
            ->attributeOptionCodeRepository
            ->getOptionCodes(CategorySetup::CATALOG_PRODUCT_ENTITY_TYPE_ID, $attributeCode, $ids);
    }

    /**
     * @param array $result
     * @return array
     */
    private function getIdsFromValuesData(array $values): array
    {
        return array_map(function ($item) {
            return $item->getData('id');
        }, $values);
    }

    /**
     * @param array $optionCode
     * @param array $values
     * @return array
     */
    private function mapOptionCodeWithValuesData(array $optionCode, array $values): array
    {
        return array_map(function ($item) use ($optionCode) {
            array_key_exists($item->getData('id'), $optionCode) ?
                $item->setData('option_code', $optionCode[$item['id']]) :
                $item->setData('option_code', " ");
            return $item;
        }, $values);
    }

    /**
     * @return AbstractAttribute
     */
    private function getAttributeObject()
    {
        return $this->registry->registry('entity_attribute');
    }
}
