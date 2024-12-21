//
//  DebugMenuViewController.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 04.12.2024.
//

import Foundation
import UIKit

class DebugMenuViewController: UIViewController, UITableViewDelegate, UITableViewDataSource {
    private let tableView = UITableView()
    private let buttonTitles = ["Set Point", "SetABCoordinator", "Many Points", "Many Point Show Route", "Client Scenario", "Test Polyline"]
    private var setABCoordinator: SetABCoordinator!
    private var manyPointsCoordinator: ManyPointsCoordinator!
    
    override func viewDidLoad() {
        super.viewDidLoad()
        view.backgroundColor = .white
        setupTableView()
    }
    
    private func setupTableView() {
        tableView.delegate = self
        tableView.dataSource = self
        tableView.register(UITableViewCell.self, forCellReuseIdentifier: "Cell")
        tableView.frame = view.bounds
        view.addSubview(tableView)
    }
    
    func tableView(_ tableView: UITableView, numberOfRowsInSection section: Int) -> Int {
        return buttonTitles.count
    }
    
    func tableView(_ tableView: UITableView, cellForRowAt indexPath: IndexPath) -> UITableViewCell {
        let cell = tableView.dequeueReusableCell(withIdentifier: "Cell", for: indexPath)
        cell.textLabel?.text = buttonTitles[indexPath.row]
        return cell
    }
    
    func tableView(_ tableView: UITableView, didSelectRowAt indexPath: IndexPath) {
        tableView.deselectRow(at: indexPath, animated: true)
        switch indexPath.row {
        case 0:
            let setPointVC = SetPointViewController()
            setPointVC.didTapDoneWithPoint = {
                [weak self] in
                guard let self = self else { return }
                print($0)
            }
            navigationController?.pushViewController(setPointVC, animated: true)
            
        case 1:
            self.setABCoordinator = SetABCoordinator(navigationController: navigationController ?? UINavigationController())
            self.setABCoordinator.didFinishWithAB = { [weak self] value in
                guard let self = self else { return }
                let routeShowerViewController = RouteShowerViewController(
                    aPoint: value.aPoint,
                    bPoint: value.bPoint,
                    avoidPoints: [ScenarioDataProvider.specialPoint2]
                )
                routeShowerViewController.didTapNextButtonWithJson = {
                    [weak self] json in
                    guard let self = self else { return }
                    guard let json = json else {
                        print("json is nil")
                        return
                    }
                    print(json)
                    self.navigationController?.popViewController(animated: true)
                }
                self.navigationController?.pushViewController(routeShowerViewController, animated: true)
            }
            self.setABCoordinator.startCoordinator()
        case 2:
            manyPointsCoordinator = .init(navigationController: self.navigationController ?? .init())
            manyPointsCoordinator.start()
            manyPointsCoordinator.didFinish = { points in
                print(points)
            }
            
        case 3:
            manyPointsCoordinator = .init(navigationController: self.navigationController ?? .init())
            manyPointsCoordinator.start()
            manyPointsCoordinator.didFinish = {
                [weak self] points in
                guard let self = self else { return }
                let vc = ManyPointsRouteShowerViewController(points: points)
                self.navigationController?.pushViewController(vc, animated: true)
            }
        case 4:
            let clientScenarioActions: [ScenarioAction] = [
                .waitFor(7),
                .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints1)),
                .showSubRoute(
                    .init(
                        directionCoordinates: ScenarioDataProvider.abPoints1,
                        movingPart: .init(
                            movingCoordinatesDropAmount: 5,
                            movingTime: 5
                        )
                    )
                ),
                .waitFor(2),
                .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint1)),
                .waitFor(2),
                .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint2)),
                .waitFor(1),
                .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints2)),
                .showSubRoute(
                    .init(
                        directionCoordinates: ScenarioDataProvider.abPoints2,
                        movingPart: nil
                    )
                ),
                .waitFor(2),
                .showPoint(.init(color: .systemRed, coordinate: ScenarioDataProvider.specialPoint3)),
                .waitFor(1),
                .showRoute(.init(directionCoordinates: ScenarioDataProvider.abPoints3)),
                .showSubRoute(
                    .init(
                        directionCoordinates: ScenarioDataProvider.abPoints3,
                        movingPart: nil
                    )
                ),
                .waitFor(2),
                .showYourRouteAreDeletedAlert
            ]
            let vc = RouteViewControllerScenario(scenario: clientScenarioActions)
            self.navigationController?.pushViewController(vc, animated: true)
            
        case 5:
            break 
        default: break
        }
    }
}
