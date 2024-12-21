

Auth Service
============

Implemented -> Main Service (/service)


Main Service
============

- Main Service (Sensors, Routes, Tracks, Groups)

- Auth Service (User Login/Register by email, User Login/Register with Apple, Google, Roles/Users Privileges to objects)


                                 Dependency
                                 ----------

                        Groups -->  Users  <--  Sensors

                           ^         ^             ^
                           |         |             |
                            -----  Routes        Tracks

                                     ^             ^
                                     |             |
                                 Route Parts   Track Parts

