<?php

namespace App\Form\Transformer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Class EntityToIdTransformer
 * @package App\Form\Transformer
 */
class EntityToIdTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $em;
    /**
     * @var string
     */
    private $dataClass;

    /**
     * EntityToIdTransformer constructor.
     * @param EntityManagerInterface $em
     * @param string $dataClass
     */
    public function __construct(EntityManagerInterface $em, string $dataClass)
    {
        $this->em = $em;
        $this->dataClass = $dataClass;
    }

    /**
     * @param mixed $value
     * @return mixed|string
     */
    public function transform($value)
    {
        if (empty($value)) {
            return '';
        }
        if (!$value instanceof $this->dataClass) {
            throw new UnexpectedTypeException($value, $this->dataClass);
        }
        if (!$this->em->getUnitOfWork()->isInIdentityMap($value)) {
            throw new \InvalidArgumentException('Entities passed to the choice field must be managed');
        }
        $ids = $this->em->getUnitOfWork()->getEntityIdentifier($value);
        if (count($ids) == 1) {
            $id = [reset($ids)];
        } else {
            $id = [];
            foreach($ids as $k => $v) {
                $id[] = $k;
                $id[] = $v;
            }
        }

        return implode('|', $id);
    }

    /**
     * @param mixed $value
     * @return mixed|null|object
     */
    public function reverseTransform($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = explode('|', $value);
        if (count($value) > 1 && count($value) % 2 != 0) {
            throw new \InvalidArgumentException('Incorrect identifier ' . $value);
        }
        if (count($value) == 1) {
            // Не буду усложнять
            $id = ['id' => reset($value)];
        } else {
            $id = [];
            $key = null;
            foreach ($value as $item) {
                if (is_null($key)) {
                    $key = $item;
                } else {
                    $id[$key] = $item;
                    $key = null;
                }
            }
        }
        $object = $this->em->getRepository($this->dataClass)->findOneBy($id);
        if (null === $object) {
            throw new TransformationFailedException('The entity could not be found');
        }

        return $object;
    }

}