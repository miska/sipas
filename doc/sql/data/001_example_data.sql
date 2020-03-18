DELETE FROM pastes;

DELETE FROM paste_datas;

INSERT INTO pastes VALUES (
    '007',
    'Public Secret',
    'Bond, James Bond',
    'text',
    False,
    9999997,
    0,
    '127.0.0.1'),(
    '12345',
    'Lorem Ipsum',
    'Richard McClintock',
    'text',
    False,
    9999999,
    0,
    '127.0.0.1'),(
    '999',
    'To be deleted',
    'Ghost',
    'text',
    False,
    9999998,
    3600,
    '127.0.0.1'),(
    'c',
    'C example',
    'Dennis Ritchie',
    'c',
    False,
    9999999,
    0,
    '127.0.0.1'
);

INSERT INTO paste_datas VALUES (
    '007',
    'There is nothing here'),(
    '12345',
    'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.'),(
    '999',
    'Should disappear'),(
    'c',
    '#include <stdio.h>

int main(int argc, char** argv) {
    return 0;
}'
);
