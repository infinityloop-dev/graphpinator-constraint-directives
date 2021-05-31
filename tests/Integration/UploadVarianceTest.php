<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class UploadVarianceTest extends \PHPUnit\Framework\TestCase
{
    public function covarianceDataProvider() : array
    {
        return [
            [
                ['maxSize' => 5000],
                ['maxSize' => 5000],
                null,
            ],
            [
                ['maxSize' => 5000],
                [],
                null,
            ],
            [
                ['maxSize' => 5000],
                ['maxSize' => 6000],
                null,
            ],
            [
                ['mimeType' => ['text/plain', 'text/html']],
                ['mimeType' => ['text/plain', 'text/html']],
                null,
            ],
            [
                ['mimeType' => ['text/plain']],
                ['mimeType' => ['text/plain', 'text/html']],
                null,
            ],
            [
                ['maxSize' => 5000],
                ['maxSize' => 4000],
                \Graphpinator\Exception\Type\ArgumentDirectiveNotContravariant::class,
            ],
            [
                ['mimeType' => ['text/plain', 'text/html']],
                ['mimeType' => ['text/plain']],
                \Graphpinator\Exception\Type\ArgumentDirectiveNotContravariant::class,
            ],
            [
                [],
                ['maxSize' => 4000],
                \Graphpinator\Exception\Type\ArgumentDirectiveNotContravariant::class,
            ],
        ];
    }

    /**
     * @dataProvider covarianceDataProvider
     * @param array $parent
     * @param array $child
     * @param string|null $exception
     */
    public function testCovariance(array $parent, array $child, ?string $exception) : void
    {
        $interface = new class($parent) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'uploadField',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        \Graphpinator\Argument\Argument::create(
                            'file',
                            new \Graphpinator\Module\Upload\UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->directiveArgs),
                    ]))
                ]);
            }

            public function createResolvedValue(mixed $rawValue): \Graphpinator\Value\TypeIntermediateValue
            {
            }
        };
        $type = new class($interface, $child) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                \Graphpinator\Type\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Utils\InterfaceSet([$interface]));
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'uploadField',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        \Graphpinator\Argument\Argument::create(
                            'file',
                            new \Graphpinator\Module\Upload\UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->directiveArgs),
                    ]))
                ]);
            }

            public function createResolvedValue(mixed $rawValue): \Graphpinator\Value\TypeIntermediateValue
            {
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(\Graphpinator\Field\FieldSet::class, $type->getFields());
        }
    }

    public static function getUploadType() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'UploadType';

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField(
                        'fileContent',
                        \Graphpinator\Container\Container::String(),
                        static function (?\Psr\Http\Message\UploadedFileInterface $file) : string {
                            return $file->getStream()->getContents();
                        },
                    ),
                ]);
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }
        };
    }
}
