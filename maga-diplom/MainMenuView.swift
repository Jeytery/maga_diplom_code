//
//  MainMenuView.swift
//  maga-diplom
//
//  Created by Dmytro Ostapchenko on 20.11.2024.
//

import SwiftUI

enum MainMenuEvent {
    case startRoute
    case setObstaclePoint
}

class MainMenuViewModel: ObservableObject {
    var eventOutputHandler: ((MainMenuEvent) -> Void)?
    
    func startRouteTapped() {
        eventOutputHandler?(.startRoute)
    }
    
    func setObstaclePointTapped() {
        eventOutputHandler?(.setObstaclePoint)
    }
}

struct MainMenuView: View {
    @ObservedObject var viewModel: MainMenuViewModel

    var body: some View {
        NavigationView {
            VStack(spacing: 16) {
                Button(action: {
                    viewModel.startRouteTapped()
                }) {
                    HStack {
                        Text("Start Route")
                            .font(.headline)
                            .foregroundColor(.white)
                        Spacer()
                        Image(systemName: "arrow.right")
                            .foregroundColor(.white)
                    }
                    .padding()
                    .frame(maxWidth: .infinity)
                    .background(Color.blue)
                    .cornerRadius(12)
                }
                .padding(.horizontal)

                Button(action: {
                    viewModel.setObstaclePointTapped()
                }) {
                    HStack {
                        Text("Set Obstacle Point")
                            .font(.headline)
                            .foregroundColor(.white)
                        Spacer()
                        Image(systemName: "arrow.right")
                            .foregroundColor(.white)
                    }
                    .padding()
                    .frame(maxWidth: .infinity)
                    .background(Color.blue)
                    .cornerRadius(12)
                }
                .padding(.horizontal)

                Spacer()
            }
            .padding(.top, 20)
            .navigationTitle("Main menu")
        }
    }
}

#Preview {
    MainMenuView(viewModel: .init())
}
