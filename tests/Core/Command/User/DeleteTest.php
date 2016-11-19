<?php
/**
 * @author Joas Schilling <nickvergessen@owncloud.com>
 *
 * @copyright Copyright (c) 2015, ownCloud, Inc.
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace Tests\Core\Command\User;


use OC\Core\Command\User\Delete;
use Test\TestCase;

class DeleteTest extends TestCase {
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $userManager;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleInput;
	/** @var \PHPUnit_Framework_MockObject_MockObject */
	protected $consoleOutput;

	/** @var \Symfony\Component\Console\Command\Command */
	protected $command;

	protected function setUp() {
		parent::setUp();

		$userManager = $this->userManager = $this->getMockBuilder('OCP\IUserManager')
			->disableOriginalConstructor()
			->getMock();
		$this->consoleInput = $this->createMock('Symfony\Component\Console\Input\InputInterface');
		$this->consoleOutput = $this->createMock('Symfony\Component\Console\Output\OutputInterface');

		/** @var \OCP\IUserManager $userManager */
		$this->command = new Delete($userManager);
	}


	public function validUserLastSeen() {
		return [
			[true, 'The specified user was deleted'],
			[false, 'The specified user could not be deleted'],
		];
	}

	/**
	 * @dataProvider validUserLastSeen
	 *
	 * @param bool $deleteSuccess
	 * @param string $expectedString
	 */
	public function testValidUser($deleteSuccess, $expectedString) {
		$user = $this->createMock('OCP\IUser');
		$user->expects($this->once())
			->method('delete')
			->willReturn($deleteSuccess);

		$this->userManager->expects($this->once())
			->method('get')
			->with('user')
			->willReturn($user);

		$this->consoleInput->expects($this->once())
			->method('getArgument')
			->with('uid')
			->willReturn('user');

		$this->consoleOutput->expects($this->once())
			->method('writeln')
			->with($this->stringContains($expectedString));

		self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}

	public function testInvalidUser() {
		$this->userManager->expects($this->once())
			->method('get')
			->with('user')
			->willReturn(null);

		$this->consoleInput->expects($this->once())
			->method('getArgument')
			->with('uid')
			->willReturn('user');

		$this->consoleOutput->expects($this->once())
			->method('writeln')
			->with($this->stringContains('User does not exist'));

		self::invokePrivate($this->command, 'execute', [$this->consoleInput, $this->consoleOutput]);
	}
}