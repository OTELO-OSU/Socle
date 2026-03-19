<?php

namespace App\Validator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class EntityExistsValidator extends ConstraintValidator
{
    public function __construct(private EntityManagerInterface $em) {}

    public function validate($value, Constraint $constraint): void
    {
        if ($value === null || $value === '') {
            return; // Laisser d'autres contraintes gérer NotBlank si nécessaire
        }

        $repo = $this->em->getRepository($constraint->entity);

        // Build criteria: primary field => value, and optional extra field => extraValue
        $criteria = [$constraint->field => $value];
        if (isset($constraint->extraField) && $constraint->extraField !== null) {
            $criteria[$constraint->extraField] = $constraint->extraValue;
        }

        $found = $repo->findOneBy($criteria);

        if (!$found) {
            // Build a clearer message when extraField is used
            if (isset($constraint->extraField) && $constraint->extraField !== null) {
                $entityShort = (new \ReflectionClass($constraint->entity))->getShortName();
                $message = sprintf(
                    "L'entité %s avec %s='%s' et %s='%s' n'existe pas.",
                    $entityShort,
                    $constraint->field,
                    (string)$value,
                    $constraint->extraField,
                    is_bool($constraint->extraValue) ? ($constraint->extraValue ? 'true' : 'false') : (string)$constraint->extraValue
                );

                $this->context
                    ->buildViolation($message)
                    ->addViolation();
            } else {
                $this->context
                    ->buildViolation($constraint->message)
                    ->setParameter('{{ entity }}', (new \ReflectionClass($constraint->entity))->getShortName())
                    ->setParameter('{{ field }}', $constraint->field)
                    ->setParameter('{{ value }}', (string)$value)
                    ->addViolation();
            }
        }
    }
}
