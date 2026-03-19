<?php

namespace App\Validator;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class EntityExists extends Constraint
{
    public string $message = "L'entité {{ entity }} avec {{ field }}='{{ value }}' n'existe pas.";
    public string $entity;
    public string $field = 'id';
    public ?string $extraField = null;
    public mixed $extraValue = true;

    public function __construct(string $entity, ?string $field = null, ?string $extraField = null, mixed $extraValue = true, ?string $message = null, array $groups = null, mixed $payload = null)
    {
        parent::__construct([], $groups, $payload);
        $this->entity = $entity;
        if ($field) $this->field = $field;
        if ($extraField) $this->extraField = $extraField;
        if ($extraValue !== null) $this->extraValue = $extraValue;
        if ($message) $this->message = $message;
    }

    public function validatedBy(): string
    {
        return EntityExistsValidator::class;
    }
}
