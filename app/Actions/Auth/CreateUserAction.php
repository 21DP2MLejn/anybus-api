<?php

namespace App\Actions\Auth;

use App\DTO\Auth\RegisterDTO;
use App\Models\User;
use App\Repositories\User\UserRepositoryInterface;

class CreateUserAction
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Create a new user.
     *
     * @param  RegisterDTO  $dto
     * @return User
     */
    public function execute(RegisterDTO $dto): User
    {
        return $this->userRepository->create($dto->toArray());
    }
}

