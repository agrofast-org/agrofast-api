<?php

namespace Tests\Feature\Requests\User;

use App\Http\Requests\User\UserUpdateRequest;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserUpdateRequestTest extends TestCase
{
    #[Test]
    public function it_should_fail_when_updating_with_nonexistent_document_id()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                ['id' => -1],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.id', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_creating_document_with_invalid_type()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                [
                    'number' => '12345678900',
                    'type' => 1234 // tipo inexistente
                ]
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_pass_with_valid_structure_but_invalid_references()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                [
                    'number' => '12345678900',
                    'type' => 9999, // tipo inexistente
                ],
                [
                    'id' => 8888 // também inexistente
                ],
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
        $this->assertArrayHasKey('documents.1.id', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_documents_is_missing()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_document_is_empty()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents', $validator->errors()->toArray());
    }

    #[Test]
    public function it_should_fail_when_creating_document_without_required_fields()
    {
        $data = [
            'name' => 'Murilo',
            'surname' => 'Figueiredo',
            'documents' => [
                []
            ],
        ];

        $validator = Validator::make($data, (new UserUpdateRequest())->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('documents.0.number', $validator->errors()->toArray());
        $this->assertArrayHasKey('documents.0.type', $validator->errors()->toArray());
    }
}
