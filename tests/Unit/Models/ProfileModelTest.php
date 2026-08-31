<?php

namespace Tests\Unit\Models;

use App\Models\Department;
use App\Models\Cohort;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_can_be_created_with_factory(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create();

        $this->assertDatabaseHas('profiles', [
            'id' => $profile->id,
            'profileable_type' => User::class,
            'profileable_id' => $user->id,
        ]);
    }

    public function test_profile_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $profile->user);
        $this->assertEquals($user->id, $profile->user->id);
    }

    public function test_profile_belongs_to_department(): void
    {
        $department = Department::factory()->create();
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create([
            'department_id' => $department->id,
        ]);

        $this->assertInstanceOf(Department::class, $profile->department);
    }

    public function test_profile_belongs_to_cohort(): void
    {
        $cohort = Cohort::factory()->create();
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create([
            'cohort_id' => $cohort->id,
        ]);

        $this->assertInstanceOf(Cohort::class, $profile->cohort);
    }

    public function test_profile_uses_morph_to_for_profileable(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create();

        $this->assertInstanceOf(User::class, $profile->profileable);
        $this->assertEquals($user->id, $profile->profileable->id);
    }

    public function test_has_sufficient_balance_for_returns_true_when_balance_is_enough(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create(['leave_balance' => 10.0]);

        $this->assertTrue($profile->hasSufficientBalanceFor(5.0));
    }

    public function test_has_sufficient_balance_for_returns_false_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create(['leave_balance' => 3.0]);

        $this->assertFalse($profile->hasSufficientBalanceFor(5.0));
    }

    public function test_has_sufficient_balance_for_returns_true_when_balance_equals_days(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create(['leave_balance' => 5.0]);

        $this->assertTrue($profile->hasSufficientBalanceFor(5.0));
    }

    public function test_for_department_scope_filters_by_department_id(): void
    {
        $dept1 = Department::factory()->create();
        $dept2 = Department::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $deptProfile = Profile::factory()->forUser($user1)->create(['department_id' => $dept1->id]);
        Profile::factory()->forUser($user2)->create(['department_id' => $dept2->id]);

        $results = Profile::forDepartment($dept1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($dept1->id, $results->first()->department_id);
    }

    public function test_for_cohort_scope_filters_by_cohort_id(): void
    {
        $cohort1 = Cohort::factory()->create();
        $cohort2 = Cohort::factory()->create();
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $cohortProfile = Profile::factory()->forUser($user1)->create(['cohort_id' => $cohort1->id]);
        Profile::factory()->forUser($user2)->create(['cohort_id' => $cohort2->id]);

        $results = Profile::forCohort($cohort1->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($cohort1->id, $results->first()->cohort_id);
    }

    public function test_active_scope_filters_active_profiles(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $activeProfile = Profile::factory()->forUser($user1)->create(['status' => 'active']);
        Profile::factory()->forUser($user2)->create(['status' => 'graduated']);

        $results = Profile::active()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('active', $results->first()->status);
    }

    public function test_graduated_scope_filters_graduated_profiles(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $graduatedProfile = Profile::factory()->forUser($user1)->create(['status' => 'graduated']);
        Profile::factory()->forUser($user2)->create(['status' => 'active']);

        $results = Profile::graduated()->get();

        $this->assertCount(1, $results);
        $this->assertEquals('graduated', $results->first()->status);
    }

    public function test_by_profileable_scope_filters_by_user_id_and_type(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create();
        Profile::factory()->create([
            'profileable_type' => \App\Models\Staff::class,
            'profileable_id' => 999,
        ]);

        $results = Profile::byProfileable($user->id)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($profile->id, $results->first()->id);
    }

    public function test_casts_date_of_birth_to_date(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create([
            'date_of_birth' => '2000-01-15',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $profile->date_of_birth);
        $this->assertEquals('2000-01-15', $profile->date_of_birth->format('Y-m-d'));
    }

    public function test_casts_dietary_restrictions_to_array(): void
    {
        $user = User::factory()->create();
        $profile = Profile::factory()->forUser($user)->create([
            'dietary_restrictions' => ['vegetarian', 'gluten-free'],
        ]);

        $this->assertIsArray($profile->dietary_restrictions);
        $this->assertContains('vegetarian', $profile->dietary_restrictions);
        $this->assertContains('gluten-free', $profile->dietary_restrictions);
    }

    public function test_fillable_attributes_are_settable(): void
    {
        $user = User::factory()->create();
        $profile = new Profile();
        $profile->profileable_type = User::class;
        $profile->profileable_id = $user->id;
        $profile->first_name = 'John';
        $profile->last_name = 'Doe';
        $profile->date_of_birth = '2000-01-15';
        $profile->phone = '+1234567890';
        $profile->save();

        $this->assertDatabaseHas('profiles', [
            'profileable_type' => User::class,
            'profileable_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);
    }
}
