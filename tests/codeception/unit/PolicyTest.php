<?php

namespace todo\unit;

use Codeception\Test\Unit;
use humhub\modules\todo\services\RecurrencePolicy;
use humhub\modules\todo\services\ReminderPolicy;
use humhub\modules\todo\services\TaskAuthorizationService;

final class PolicyTest extends Unit
{
    public function testTaskAuthorizationRoles(): void
    {
        $this->assertTrue(TaskAuthorizationService::canManage(true, false));
        $this->assertTrue(TaskAuthorizationService::canManage(false, true));
        $this->assertFalse(TaskAuthorizationService::canManage(false, false));
        $this->assertTrue(TaskAuthorizationService::canWorkOn(false, false, true));
        $this->assertFalse(TaskAuthorizationService::canDelete(false, false));
    }

    public function testMonthlyRecurrenceClampsEndOfMonth(): void
    {
        $this->assertSame('2026-02-28', RecurrencePolicy::nextDate('2026-01-31', 'monthly'));
        $this->assertTrue(RecurrencePolicy::isWithinEndDate('2026-02-28', '2026-02-28'));
        $this->assertFalse(RecurrencePolicy::isWithinEndDate('2026-03-01', '2026-02-28'));
    }

    public function testReminderStagesAreSelectedOnlyOnce(): void
    {
        $this->assertSame(ReminderPolicy::UPCOMING, ReminderPolicy::determine(
            '2026-10-03',
            '2026-10-01',
            3,
            true,
            true,
            true,
            false,
            false,
            false
        ));
        $this->assertNull(ReminderPolicy::determine(
            '2026-10-03',
            '2026-10-01',
            3,
            true,
            true,
            true,
            true,
            false,
            false
        ));
    }
}
